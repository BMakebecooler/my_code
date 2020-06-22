<?php

/**
 * php ./yii sync/api/onair
 */

namespace console\controllers\sync\api;

use common\helpers\Dates;
use common\models\cmsContent\CmsContentElement;
use Exception;
use modules\api\models\mongodb\Onair;
use modules\shopandshow\lists\Onair as OnairList;
use modules\api\models\mongodb\product\Product;
use modules\shopandshow\models\mediaplan\AirBlock;
use yii\helpers\Console;

/**
 * Class OnairController
 * @package console\controllers
 */
class OnairController extends \yii\console\Controller
{

    public function actionIndex()
    {
        $onAirSchedule = OnairList::getScheduleList(); //Dates::beginOfDate(1533754800)

        if (!$onAirSchedule) {
            $this->stdout("no onAirSchedule\n", Console::FG_RED);
            return false;
        }

        $mongoDB = \Yii::$app->mongodb->createCommand();

        $onairProducts = [];

        /**
         * @var $category AirBlock
         * @var $product CmsContentElement
         */
        foreach ($onAirSchedule as $category) {

            $products = [];

            if ($data = $category->cmsContentElements) {
                foreach ($data as $product) {
                    $products[] = $product->id;

                    if ($data = Product::getData($product)) {
                        $onairProducts[] = $data;
                    }
                }
            }

            $categoryInfo = [
                'id' => $category->id,
                'name' => $category->getCategoryName(),
                'begin_datetime' => $category->begin_datetime,
                'end_datetime' => $category->end_datetime,
                'current' => false, //Подумать над другим способом
                'time' => sprintf('%s - %s', date('H:00', $category->begin_datetime), date('H:00', $category->end_datetime)),
                'block_id' => $category->block_id,
                'products' => $products,
            ];

            $mongoDB->addUpdate(['id' => $category->id], $categoryInfo, ['upsert' => true]);
        }

        $mongoDB->executeBatch(Onair::collectionName());

        foreach ($onairProducts as $product) {
            $mongoDB->update(Product::collectionName(), ['id' => $product['id']], $product, ['upsert' => true]);

            $this->stdout(sprintf("update:%s\n", $product['id']), Console::FG_GREEN);
        }

        return false;
    }


}