<?php


namespace modules\api\controllers\v1\schedule;


use common\components\cache\PageCache;
use common\helpers\ArrayHelper;
use common\helpers\CacheHelper;
use common\helpers\Dates;
use modules\api\resource\schedule\Hours;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;

class HoursController extends ActiveController
{
    public $modelClass = 'modules\api\resource\Hours';

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
            'index' => ['GET'], //aka days
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
        $query = Hours::find()
            ->byDay(Dates::getDaytimeFromId(\Yii::$app->request->get('dayId', 0)))
            ->orderBy('begin_datetime');

        if ($categoryId = \Yii::$app->request->get('categoryId')){
            $query->byCategoryId($categoryId);
        }

        return new ActiveDataProvider(['query' => $query]);
    }
}