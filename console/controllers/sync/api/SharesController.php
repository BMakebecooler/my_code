<?php

/**
 * php ./yii sync/api/shares
 */

namespace console\controllers\sync\api;

use common\helpers\Url;
use modules\api\models\mongodb\product\Product;
use modules\api\models\mongodb\Share;
use modules\shopandshow\models\shares\SsShare;
use modules\shopandshow\models\shares\SsShareProduct;
use skeeks\cms\components\Cms;
use yii\console\Exception;
use yii\helpers\Console;

/**
 * Class SharesController
 * @package console\controllers
 */
class SharesController extends \yii\console\Controller
{

    public function actionIndex()
    {
        $shares = SsShare::find()
            ->joinWith(['shareProducts', 'product'])
            ->andWhere(['ss_shares.active' => Cms::BOOL_Y])
            ->andWhere('begin_datetime >= :time', [
                ':time' => time(), //1524283200
            ])->all();

        $mongoDB = \Yii::$app->mongodb->createCommand();

        $count = 0;

        $shareProducts = [];

        /**
         * @var $share SsShare
         * @var $shareProduct SsShareProduct
         */
        foreach ($shares as $share) {

            $products = [];

            if ($data = $share->getShareProducts()->joinWith('product')->all()) {
                foreach ($data as $shareProduct) {
                    $products[] = $shareProduct->product->id;
                    if ($data = Product::getData($shareProduct->product)) {
                        $shareProducts[] = $data;
                    }
                }
            } elseif ($product = $share->product) {
                $products[] = $share->product->id;
                if ($data = Product::getData($product)) {
                    $shareProducts[] = $data;
                }
            }

            $shareImage = null;
            if ($src = $share->getImageSrc()) {
                $shareImage = ['src' => sprintf('%s%s', Url::getBaseUrl(), $src)];
            }

            $shareInfo = [
                'share_id' => $share->id,
                'share_name' => $share->name,
                'share_description' => $share->description,
                'share_code' => $share->code,
                'promo_type' => $share->promo_type,
                'banner_type' => $share->banner_type,
                'share_url' => $share->url,
                'share_image' => $shareImage,
                'products' => $products,
                'begin_datetime' => $share->begin_datetime,
                'end_datetime' => $share->end_datetime,
            ];

            $mongoDB->addUpdate(['id' => $share->id], $shareInfo, ['upsert' => true]);

            ++$count;
        }

        if ($count) {
            $this->stdout("Добавлено акций " . $count . "\n", Console::FG_GREEN);
            return $mongoDB->executeBatch(Share::collectionName());
        }

        if ($shareProducts) {
            try {
                $mongoDB->batchInsert(Product::collectionName(), $shareProducts);
            } catch (Exception $exception) {
                $this->stdout($exception->getMessage(), Console::FG_RED);

                foreach ($shareProducts as $product) {
                    $mongoDB->update(Product::collectionName(), ['id' => $product['id']], $product, ['upsert' => true]);
                    $this->stdout(sprintf("update:%s\n", $product['id']), Console::FG_GREEN);
                }
            }
        }


        $this->stdout("Нет акций \n", Console::FG_RED);

        return [];
    }

}