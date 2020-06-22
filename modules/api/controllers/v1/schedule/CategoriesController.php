<?php


namespace modules\api\controllers\v1\schedule;


use common\components\cache\PageCache;
use common\helpers\CacheHelper;
use common\helpers\Dates;
use modules\api\resource\schedule\Category;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\rest\ActiveController;

class CategoriesController extends ActiveController
{
    public $modelClass = 'modules\api\resource\Category';

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
            'index' => ['GET'],
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
        return new ActiveDataProvider([
            'query' => Category::find()
                ->byDay(Dates::getDaytimeFromId(\Yii::$app->request->get('dayId', 0)))
                ->groupBy('section_id')
                ->orderBy('begin_datetime')
        ]);
    }
}