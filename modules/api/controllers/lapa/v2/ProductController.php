<?php


namespace modules\api\controllers\lapa\v2;


use common\components\cache\PageCache;
use common\helpers\CacheHelper;
use modules\api\resource\lapa\v2\Product;
use modules\api\resource\lapa\v2\Variation;
use yii\data\ActiveDataProvider;
use yii\filters\Cors;
use yii\rest\ActiveController;

class ProductController extends ActiveController
{

    public $modelClass = 'modules\api\resource\lapa\v2\Product';

    public function behaviors()
    {
        return [
            [
                'class' => \yii\filters\ContentNegotiator::className(),
                'formats' => [
                    'application/json' => \yii\web\Response::FORMAT_JSON,
                ],
            ],
            [
                'class' => Cors::className(),
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'HEAD', 'OPTIONS'],
                ],
            ],
            [
                'class' => PageCache::class,
                'duration' => CacheHelper::CACHE_TIME_PRODUCT,
                'variations' => CacheHelper::getProductViaApiVariation(),
                'enabled' => CacheHelper::isEnabled()
            ],
        ];
    }

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

    public function prepareDataProvider()
    {
        $productId = (int)\Yii::$app->request->get('id');

        if (!$productId) {
            return [];
        }

        return Product::find()
            //->canSale() //Фильтровать в данном месте избыточно
            ->andWhere(['id' => $productId])
            ->one();
    }

    public function actionView($id){
        return new ActiveDataProvider([
            'query' => Product::find()->andWhere(['id' => $id])
        ]);
    }

    /**
     * Выборка и возврат списка модификаций для указанного лота
     */
    public function actionVariations()
    {
        $productId = (int)\Yii::$app->request->get('id');

        if (!$productId) {
            return [];
        }

        return new ActiveDataProvider([
            'query' => Variation::getProductOffersCanSaleQuery($productId),
            'pagination' => false,
        ]);
    }
}