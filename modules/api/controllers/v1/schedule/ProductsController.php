<?php


namespace modules\api\controllers\v1\schedule;


use common\components\cache\PageCache;
use common\helpers\ArrayHelper;
use common\helpers\CacheHelper;
use common\models\SsMediaplanAirDayProductTime;
use modules\api\resource\schedule\Product;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use yii\web\Response;

class ProductsController extends ActiveController
{
    public $modelClass = 'modules\api\resource\schedule\Product';

    public function behaviors()
    {
        return [
            [
                'class' => \yii\filters\ContentNegotiator::className(),
                'only' => ['index'],
                'formats' => [
                    'application/json' => \yii\web\Response::FORMAT_JSON,
                ],
            ],
            [
                'class' => PageCache::class,
                'only' => ['index'],
                'duration' => CacheHelper::CACHE_TIME_ONAIR_API,
                'variations' => CacheHelper::getOnAirApiVariation(),
                'enabled' => CacheHelper::isEnabled()
            ],
        ];
    }

    public function verbs()
    {
        $verbs = [
            'index' => ['GET']
        ];

        return ArrayHelper::merge(parent::verbs(), $verbs);
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
        $airProductsModel = new SsMediaplanAirDayProductTime();
        $airProductsModel->load(\Yii::$app->request->get(), '');

        $airProducts = $airProductsModel->getAirProducts();

        $query = Product::find()
            ->canSale()
            ->andWhere(['cms_content_element.id' => ArrayHelper::getColumn($airProducts, 'lot_id')]);

        return new ActiveDataProvider([
            'query' => $query
        ]);
    }
}