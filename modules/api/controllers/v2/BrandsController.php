<?php

namespace modules\api\controllers\v2;

use common\components\cache\PageCache;
use common\helpers\Brand as BrandHelper;
use common\helpers\CacheHelper;
use common\models\Brand;
use Exception;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use Yii;


class BrandsController extends ActiveController
{
    public $modelClass = \modules\api\resource\v2\Brand::class;

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
                'class' => PageCache::class,
                'duration' => CacheHelper::CACHE_TIME_BRANDS_API,
                'variations' => CacheHelper::getBrandsViaApiVariation(),
                'enabled' => CacheHelper::isEnabled()
            ],
        ];
    }

    /**
     *
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

    public function actionWithCategories()
    {
        $category = Yii::$app->request->get('category');
        if (!$category) {
            throw new Exception('Param category is empty');
        }
        $count = (int)Yii::$app->request->get('count') ?? Brand::COUNT_DEFAULT;

        $allBrands = Brand::find()
            ->onlyActive()
            ->all();

        $jsonArray = BrandHelper::prepareToApi($allBrands, $category, $count);

        sort($jsonArray['categories']);
        sort($jsonArray['brands']);

        usort($jsonArray['brands'], function ($a, $b) {
            return mb_strtoupper($a['name']) <=> mb_strtoupper($b['name']);
        });

        return $jsonArray;
    }

    public function prepareDataProvider()
    {
        $query = Brand::find();

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => false
        ]);
    }

}