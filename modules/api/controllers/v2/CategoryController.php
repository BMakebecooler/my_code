<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 26/12/2018
 * Time: 20:03
 */

namespace modules\api\controllers\v2;

use common\components\cache\PageCache;
use common\helpers\ArrayHelper;
use common\helpers\CacheHelper;
use common\models\search\CategorySearch;
use modules\api\controllers\ActiveController;
use modules\api\resource\Category;
use yii\data\ActiveDataProvider;
use yii\filters\Cors;

class CategoryController extends ActiveController
{
    public $modelClass = Category::class;

    public function behaviors()
    {
        return ArrayHelper::merge([
            [
                'class' => Cors::className(),
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'HEAD', 'OPTIONS'],
                ],
            ],
            [
                'class' => PageCache::class,
                'duration' => CacheHelper::CACHE_TIME_TREE,
                'variations' => CacheHelper::getCategoryViaApiVariation(),
                'enabled' => CacheHelper::isEnabled()
            ],
        ], parent::behaviors());
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
        $searchModel = new CategorySearch();
        return $searchModel->searchExtended(\Yii::$app->request->queryParams);
    }
}