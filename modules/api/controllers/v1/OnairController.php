<?php

/**
 * /api/v1/onair
 */

namespace modules\api\controllers\v1;

use common\components\mongo\Query;
use common\helpers\ArrayHelper;
use common\helpers\Dates;
use common\models\search\OnairSearch;
use modules\api\controllers\ActiveController;
use modules\api\models\mongodb\Onair;
use modules\api\models\mongodb\product\Product;
use yii\data\ActiveDataProvider;

class OnairController extends ActiveController
{

    public $modelClass = \modules\api\resource\Onair::class;

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

    /**
     * @return ActiveDataProvider
     */
    public function prepareDataProvider()
    {
        $searchModel = new OnairSearch();
        return $searchModel->search();
    }

    /**
     * @deprecated
     * @return array
     */
    public function actionIndex2()
    {
        $perPage = (int)$this->request->get('per_page', 24);

        $time = (int)$this->request->get('time', Dates::beginOfAirDate()); //1533754800
//        $time = (int)$this->request->get('time', 1536613200); //1536638400

        $query = new Query();
        $query->from(Onair::collectionName())
            ->andFilterCompare('begin_datetime', $time, '>=')
            ->limit($perPage);

        if ($onairHours = $query->all()) {

            $onairHours[0]['current'] = true;
            $currentTime = time();

            $query = new Query();

            foreach ($onairHours as &$hour) {

                $products = $query->from(Product::collectionName())
                    ->where([
                        'id' => ArrayHelper::arrayToInt($hour['products']),
                    ])
                    ->active()
                    ->orderBy([
                        'statistic.k_viewed' => SORT_DESC
                    ])
                    ->limit(20)->all();

                if ($hour['begin_datetime'] <= $currentTime && $hour['end_datetime'] >= $currentTime) {
                    $onairHours[0]['current'] = false;
                    $hour['current'] = true;
                    break;
                }

                $hour['products'] = $products;
            }

            return $onairHours;
        }

        return [];
    }
}