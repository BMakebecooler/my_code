<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 27/12/2018
 * Time: 11:21
 */

namespace modules\api\controllers\v1;

use common\models\search\ProductSearch;
use modules\api\controllers\ActiveController;
use modules\api\resource\Product;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\lists\Products;
use modules\shopandshow\lists\Shares;
use modules\shopandshow\models\shares\SsShare;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

class ProductController extends ActiveController
{

    public $modelClass = Product::class;

    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'prepareDataProvider' => [$this, 'prepareDataProvider']
            ]
        ];
    }

    /**
     * @return ActiveDataProvider
     */
    public function prepareDataProvider()
    {
        $searchModel = new ProductSearch();
        return $searchModel->search(\Yii::$app->request->queryParams);
    }

    /**
     * @param $id
     * @return array|ActiveDataProvider
     */
    public function actionView($id)
    {
        $searchModel = new ProductSearch();
        return $searchModel->search(['product_id' => $id]);
    }

    /**
     * Товар в эфире
     * @return array
     */
    public function actionNow()
    {
        $productOnairGuid = Products::getOnairProductId();

        $product = [];
        $result = [
            'video' => \Yii::$app->shopAndShowSettings->translationLink,
            'gu' => $productOnairGuid,
        ];

        /**
         * Если товара в эфире нет то возвращаем ЦТС
         */
        if ($productOnairGuid && ($guid = Guids::getGuid($productOnairGuid))) {
            $product = Product::find()
                ->andWhere(['cms_content_element.guid_id' => $guid->id])
                ->limit(1)
                ->one();

        } elseif ($ctsShare = Shares::getShareByTypeEfir()) {
            $product = Product::find()
                ->andWhere(['cms_content_element.bitrix_id' => $ctsShare->bitrix_product_id])
                ->limit(1)
                ->one();
        }

        if ($product) {
            return [$product->toArray() + $result];
        }

        return [$result];
    }

    /**
     * Получить товары ЦТС
     * @return array
     */
    public function actionCts()
    {
        $result = [];
        $ctsShares = Shares::getSharesByTypeEfir(SsShare::BANNER_TYPE_CTS, 3);

        foreach ($ctsShares as $share) {
            $product = Product::find()
                ->andWhere(['cms_content_element.bitrix_id' => $share->bitrix_product_id])
                ->limit(1)
                ->one();

            if (!$product) {
                continue;
            }

            $shareImage = null;
            if ($src = $share->getImageSrc()) {
//                $baseUrl = Url::getCdnUrl();
//                $shareImage = ['src' => sprintf('%s%s', $baseUrl, $src)];
                $src = isset($product->toArray()['images']) ? $product->toArray()['images'][0]['src'] : null;
                $shareImage = [
                    'src' => $src
                ];
            }

            $share = [
                'share_id' => $share->id,
                'share_name' => $share->name,
                'share_description' => $share->description,
                'share_code' => $share->code,
                'promo_type' => $share->promo_type,
                'banner_type' => $share->banner_type,
                'share_url' => $share->url,
                'share_image' => $shareImage,
                'begin_datetime' => $share->begin_datetime,
                'end_datetime' => $share->end_datetime,
            ];

            $result[] = $share + $product->toArray();
        }

        return $result;
    }

    /**
     * Получить популярные товары
     * @return array
     */
    public function actionPopular()
    {
        return (new ProductSearch())->popular(\Yii::$app->request->queryParams);
    }

    /**
     * Получить рекомендованные товары
     * @return array
     */
    public function actionRecommended()
    {
        return (new ProductSearch())->recommended(\Yii::$app->request->queryParams);
    }

    /**
     * Недавно в эфире
     * @return array
     */
    public function actionRecently()
    {
        return (new ProductSearch())->recentlyInOnair(\Yii::$app->request->queryParams);
    }

    /**
     * Поиск товаров
     * @return array|ActiveDataProvider
     */
    public function actionSearch()
    {
        $productsIds = \Yii::$app->cmsSearch->sphinxSearchIds();

        if (!$productsIds) {
            return [];
        }

        return (new ProductSearch())->search(['product_id' => $productsIds]);
    }

    /**
     * Отзывы
     * @return array
     */
    public function actionReview($id)
    {

        $sql = <<<SQL
SELECT message.id AS id, 
  message.created_at AS date_created,
  message.comments AS review,
  message.rating,
  message.user_name AS name
FROM `reviews2_message` AS message
WHERE `element_id` = :product_id 
ORDER BY `id` DESC 
LIMIT 50
SQL;
        $reviews = \Yii::$app->db->createCommand($sql, [
            ':product_id' => $id
        ])->queryAll();

        foreach ($reviews as &$review) {
            $review['review'] = Html::decode($review['review']);
            $review['rating'] = (int)$review['rating'];
            $review['date_created'] = date('Y-m-d H:i', $review['date_created']);
        }

        return $reviews;
    }

    /**
     * Выборка и возврат списка модификаций для указанного лота
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionVariations()
    {
        $productId = (int)$this->request->get('id');

        if (!$productId) {
            return [];
        }

        $searchModel = new ProductSearch();
        return $searchModel->variations(['product_id' => $productId]);
    }
}