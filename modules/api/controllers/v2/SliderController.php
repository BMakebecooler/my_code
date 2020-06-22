<?php


namespace modules\api\controllers\v2;

use common\components\cache\PageCache;
use common\helpers\CacheHelper;
use common\components\slider\Slider;
use yii\rest\Controller;
use Yii;

class SliderController extends Controller
{
    public static $viewProductItem = '@theme_common/parts/_item-content';

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
                'duration' => CacheHelper::CACHE_TIME_SLIDER_API,
                'variations' => CacheHelper::getSlidersViaApiVariation(),
                'enabled' => CacheHelper::isEnabled()
            ],
        ];
    }

    public function getProductsSliderData()
    {
        $return = [];
        $id = \Yii::$app->request->get('id');
        if (!$id) {
            \Yii::error('Param id is empty', __METHOD__);
            return [];
        }

        $identityId = \Yii::$app->request->get('identityId');
        $params = [];
        if ($identityId) {
            $params['identityId'] = $identityId;
        }

        $title = Yii::$app->slider->getTitle($id);
        $ctsRelated = Yii::$app->slider->getCtsRelated($id);
        $products = Yii::$app->slider->getData($id, $params);

        foreach ($products as $index => $product) {
            $return[] = $this->renderPartial(self::$viewProductItem, [
                'model' => $product,
                'h1' => $title,
                'index' => ++$index,
                'ctsRelated' => $ctsRelated
            ]);
        }

        return $return;
    }

    public function actionIndex()
    {
        $type = Yii::$app->request->get('type', Slider::TYPE_PRODUCTS);
        switch ($type) {
            case Slider::TYPE_PRODUCTS:
            default;
                return $this->getProductsSliderData();
                break;

        }
    }
}