<?php

/**
 * @deprecated
 * /api/v1/products/
 * /api/v1/products/now/
 * /api/v1/products/cts/
 * /api/v1/products/categories/
 * /api/v1/products/variations/
 * /api/v1/products/popular/
 * /api/v1/products/recommended/
 * /api/v1/products/search/
 * /api/v1/products/reviews/
 * /api/v1/products/recently/
 */

namespace modules\api\controllers\v1;

use common\components\mongo\Query;
use common\helpers\ArrayHelper;
use common\models\cmsContent\CmsContentElement;
use common\models\search\ProductSearch;
use modules\api\controllers\ActiveController;
use modules\api\models\mongodb\Category;
use modules\api\models\mongodb\product\Product;
use modules\api\models\mongodb\product\Variation;
use modules\api\models\mongodb\Share;
use modules\shopandshow\lists\Products;
use modules\shopandshow\models\shares\SsShare;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use \modules\api\lists\Product as ProductList;

class ProductsController extends ActiveController
{

    public $modelClass = \modules\api\resource\Product::class;

    public function verbs()
    {
        $verbs = [
            'search' => ['GET'],
            'now' => ['GET'],
            'cts' => ['GET'],
            'variations' => ['GET'],
            'popular' => ['GET'],
            'recommended' => ['GET'],
            'recently' => ['GET'],
        ];

        return ArrayHelper::merge(parent::verbs(), $verbs);
    }


    /**
     * Method is duplicate, original method  in ProductController
     * @return array
     */
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

    /*
     * Список товаров
     */
    public function actionIndex2()
    {
        $perPage = (int)$this->request->get('per_page', 10);
        $offset = (int)$this->request->get('offset', 0);

        $where = [
            'category_id' => (int)$this->request->get('category')
        ];

        $query = new Query();
        $query->from(Product::collectionName())
            ->active()
            ->limit($perPage)
            ->offset($offset);

        if ($where = array_filter($where)) {
            $query->andWhere($where);
        }

        return $query->all();
    }

    /**
     * Получить товар в эфире
     * @return array
     */
    public function actionNow()
    {
        $productOnairGuid = Products::getOnairProductId(); //'0E12122E1A7F49268EF917BE81335451';

        $result = [
            'video' => \Yii::$app->shopAndShowSettings->translationLink,
            'gu' => $productOnairGuid,
        ];

        if (!$productOnairGuid) {
            return [$result];
        }

        $product = (array)ProductList::getByCondition('guid', $productOnairGuid);

        if (array_filter($product)) {
            return [$product + $result];
        }

        return [$result];
    }

    /**
     * Получить товары ЦТС
     * @return array
     */
    public function actionCts()
    {
        $time = time(); //1524283200

        $query = new Query();
        $query->from(Share::collectionName())
            ->where([
                'banner_type' => SsShare::BANNER_TYPE_CTS
            ])
            ->andFilterCompare('begin_datetime', $time, '<=')
            ->andFilterCompare('end_datetime', $time, '>=')
            ->limit(5);

        $shares = $query->all();

        $result = [];

        foreach ($shares as $share) {

            $product = $query->from(Product::collectionName())
                ->where([
                    'id' => $share['products'],
                ])
                ->active()
                ->orderBy([
                    'statistic.k_viewed' => SORT_DESC
                ])->one();

            unset($share['products']);

            $result[] = ArrayHelper::merge($share, $product);
        }

        return $result;
    }

    /*
     * Список категорий
     */
    public function actionCategories()
    {
        return \Yii::$app->mongodb->getCollection(Category::collectionName())->find()->toArray();
    }

    /**
     * Выборка и возврат списка модификаций для указанного лота
     * @return array|bool
     */
    public function actionVariations()
    {
        $productId = (int)$this->request->get('id');

        $query = new Query();
        $query->from(Variation::collectionName())
            ->where([
                'product_id' => $productId
            ])
            ->active();

        $variations = $query->all();

        if (!$variations) {
            $childrens = CmsContentElement::find()
                ->joinWith(['relatedProperties', 'price'])
                ->andWhere(['cms_content_element.parent_content_element_id' => $productId])
                ->all();

            if (!$childrens) {
                return false;
            }

            foreach ($childrens as $children) {
                Variation::add($children);
            }

            $this->redirect(\Yii::$app->request->url);
        }

        return $variations;

    }

    /**
     * Получить популярные товары
     * @return array
     */
    public function actionPopular()
    {
        $query = new Query();
        $query->from(Product::collectionName())
            ->active()
            ->orderBy([
                'statistic.k_rating' => SORT_DESC
            ])
            ->limit(20);

        return $query->all();
    }

    /**
     * Получить рекомендованные товары
     * @return array
     */
    public function actionRecommended()
    {
        $query = new Query();
        $query->from(Product::collectionName())
            ->active()
            ->orderBy([
                'statistic.k_rnd' => SORT_DESC
            ])
            ->limit(20);

        return $query->all();
    }

    /**
     * Поиск товаров
     * @return array
     */
    public function actionSearch()
    {
        $perPage = (int)$this->request->get('per_page', 30);
        $page = (int)$this->request->get('page', 1);

        $productsIds = \Yii::$app->cmsSearch->sphinxSearchIds();

        if (!$productsIds) {
            return [];
        }

        $query = new Query();
        $query->from(Product::collectionName())
            ->active()
            ->andWhere([
                'id' => ArrayHelper::arrayToInt($productsIds)
            ])
            ->limit($perPage)
            ->offset(($page - 1) * $perPage);

        return $query->all();
    }

    /**
     * Отзывы
     * @return array
     */
    public function actionReviews()
    {
        $productId = (int)$this->request->get('id');

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
            ':product_id' => $productId
        ])->queryAll();

        foreach ($reviews as &$review) {
            $review['review'] = Html::decode($review['review']);
            $review['rating'] = (int)$review['rating'];
            $review['date_created'] = date('Y-m-d H:i', $review['date_created']);
        }

        return $reviews;
    }

    /**
     * Недавно в эфире
     * @return array
     */
    public function actionRecently()
    {
        $sql = <<<SQL
SELECT GROUP_CONCAT(lot_id SEPARATOR ', ') AS lots
FROM `ss_mediaplan_air_day_product_time` AS pt
WHERE pt.begin_datetime >= :begin_datetime AND pt.begin_datetime <= :end_datetime
SQL;

        $time = time(); // 1533754800
        $lots = \Yii::$app->db->createCommand($sql, [
            ':begin_datetime' => $time - 3600,
            ':end_datetime' => $time + 3600,
        ])->queryOne();

        if (!$lots) {
            return [];
        }

        $lotsIds = ArrayHelper::arrayToInt(explode(',', $lots['lots']));

        $query = new Query();
        $query->from(Product::collectionName())
            ->where([
                'id' => $lotsIds,
            ])
//            ->active()
            ->orderBy([
                'statistic.k_viewed' => SORT_DESC
            ])
            ->limit(20);

        return $query->all();
    }
}