<?php


namespace modules\api\controllers\v2;


use common\components\cache\PageCache;
use common\helpers\CacheHelper;
use common\helpers\Common;
use common\helpers\Promo as PromoHelper;
use common\models\CmsTree;
use common\models\Promo;
use modules\api\controllers\ActiveController;
use yii\data\ActiveDataProvider;
use yii\filters\Cors;
use Yii;

class BannersController extends ActiveController
{

    public $modelClass = \modules\api\resource\v2\PromoBanner::class;

    // grabbed from yii\rest\OptionsAction with a little work around
    private $_verbs = ['GET', 'OPTIONS'];

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
                'duration' => CacheHelper::CACHE_TIME_PROMO_API,
                'variations' => CacheHelper::getPromoBannersViaApiVariation(),
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


    public function prepareDataProvider()
    {
        $errorText = null;

        $source = Yii::$app->request->get('source');
        if (!$source) {
            $errorText = 'Param source is empty';
        }

        $id = Yii::$app->request->get('id');
        if (!$id) {
            $errorText = 'Param id is empty';
        }

        $paramsBannersQuery = [];

        if (!$errorText) {
            switch ($source) {
                case Common::CATEGORY_SOURCE:
                    $model = CmsTree::findOne($id);
                    if (!$model) {
                        $errorText = 'model CmsTree not found by id ' . $id;
                    } else {
                        $paramsBannersQuery['id_category'] = $model->id;
                        $paramsBannersQuery['pid_category'] = $model->pid;

                        $cases = [1 => 'first', 2 => 'second', 3 => 'third', 4 => 'fourth'];
                        $case = !empty($cases[$model->level]) ? $cases[$model->level] : $cases[4];

                        $paramsBannersQuery['case'] = "catalog_{$case}_level";
                    }
                    break;
                case Common::PROMO_SOURCE:
                    $model = Promo::findOne($id);
                    if (!$model) {
                        $errorText = 'model Promo not found by id ' . $id;
                    } else {
                        if ($model->tree_id_onair) {
                            $paramsBannersQuery['case'] = 'promo_with_category';
                            $paramsBannersQuery['id_category'] = $model->tree_id_onair;
                        } else {
                            $paramsBannersQuery['case'] = 'promo';
                        }
                    }

                    break;
                default:
                    $errorText = 'unknown source transmitted: ' . $source;
                    break;

            }
        }
        if ($errorText) {
            \Yii::error($errorText, __METHOD__);
            return [];
        }

        $ids = PromoHelper::getPromoBannersToCatalog($paramsBannersQuery, true);
        $query = \modules\api\resource\v2\PromoBanner::find()
            ->andWhere(['id' => $ids])
            ->onlyHaveImageBanner()
            ->orderBy(new \yii\db\Expression('rand()'));

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => false
        ]);
    }
}
