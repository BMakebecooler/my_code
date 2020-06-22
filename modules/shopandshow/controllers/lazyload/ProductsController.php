<?php

namespace modules\shopandshow\controllers\lazyload;

use common\helpers\ArrayHelper;
use common\helpers\CacheHelper;
use common\lists\Contents;
use modules\shopandshow\lists\LookBook;
use modules\shopandshow\models\shop\ShopContentElement;
use modules\shopandshow\models\shop\ShopProduct;
use Props\NotFoundException;
use skeeks\cms\base\Controller;
use skeeks\cms\helpers\RequestResponse;

/**
 * Class ProductsController
 * @package modules\shopandshow\controllers\lazyload
 */
class ProductsController extends Controller
{
    /**
     * @param $action
     *
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }


    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            [
                'class' => 'yii\filters\PageCache',
                'only' => ['photos', 'clients-feedback'],
                'duration' => CacheHelper::CACHE_TIME,
                'variations' => CacheHelper::getProductGalleryVariation(),
                'enabled' => CacheHelper::isEnabled()
            ],
        ]);
    }

    /**
     * @param $productId
     *
     * @return RequestResponse
     * @throws NotFoundException
     */
    public function actionPhotos($productId)
    {
        $rr = new RequestResponse();
        $rr->success = false;

//        if ($rr->isRequestAjaxPost()) {
//            $productId = \Yii::$app->request->post('product_id');

        if (!$productId) {
            return $rr;
        }

        $model = Contents::getContentElementById($productId);
        $product = \common\helpers\ProductHelper::instance($model); //Избавится от лишних моделей!
        $shopCmsCE = new ShopContentElement($model->toArray());
        $shopProduct = ShopProduct::getInstanceByContentElement($shopCmsCE);

        if (!$product) {
            throw new NotFoundException('Товар не найден!');
        }


        $rr->success = true;
        $rr->data = [
            'html' => $this->renderAjax('@template/modules/cms/content-element/_product/_photo_video', [
                'model' => $model,
                'product' => $product,
                'shopProduct' => $shopProduct,
                'shopCmsContentElement' => $shopCmsCE,
            ]),
        ];
//        }

        return $rr;
    }


    /**
     * Слайдер "Вы смотрели"
     * @return RequestResponse
     */
    public function actionVisitedProducts($productId)
    {
        $rr = new RequestResponse();
        $rr->success = false;

//        if ($rr->isRequestAjaxPost()) {
//            $productId = \Yii::$app->request->post('product_id');

        if (!$productId) {
            return $rr;
        }

        $model = Contents::getContentElementById($productId);
        $shopCmsCE = new ShopContentElement($model->toArray());

        $rr->success = true;
        $rr->data = [
            'html' => $shopCmsCE->getWidgetVisitedProducts(['data' => [
                'viewFile' => '@site/widgets/ContentElementsCms/sliders/products_6',
                'slider-option' => [
                    'infinite' => false
                ]
            ]])
        ];
//        }

        return $rr;
    }

    /**
     * Блок с этим товаром покупают
     * @return RequestResponse
     */
    public function actionFinishYourImage($productId)
    {
        $rr = new RequestResponse();
        $rr->success = false;

//        if ($rr->isRequestAjaxPost()) {
//            $productId = \Yii::$app->request->post('product_id');

        if (!$productId) {
            return $rr;
        }

        $model = Contents::getContentElementById($productId);
        $shopCmsCE = new ShopContentElement($model->toArray());

        $title = $model->relatedPropertiesModel->getAttribute('ZAGOLOVOK_CTS');
        $rr->success = true;
        $rr->data = [
            'html' => $shopCmsCE->getWidgetFinishYourImage([
                'label' => $title ?: 'С этим товаром покупают'
            ])
        ];
//        }

        return $rr;
    }

    /**
     * @return RequestResponse
     */
    public function actionClientsFeedback($productId)
    {
        $rr = new RequestResponse();
        $rr->success = false;

//        if ($rr->isRequestAjaxPost()) {
//            $productId = \Yii::$app->request->post('product_id');

        if (!$productId) {
            return $rr;
        }

        $model = Contents::getContentElementById($productId);

        $rr->success = true;
        $rr->data = [
            'html' => $this->renderAjax('@template/modules/cms/content-element/_product/_comments', [
                'model' => $model,
            ]),
        ];
//        }

        return $rr;
    }

    /**
     * @return RequestResponse
     */
    public function actionLookbookSet()
    {
        $rr = new RequestResponse();
        $rr->success = false;

        if ($rr->isRequestAjaxPost()) {
            $productId = \Yii::$app->request->post('product_id');

            if (!$productId) {
                return $rr;
            }

            if ($model = LookBook::getLbByProductId($productId)) {
                $rr->success = true;
                $rr->data = [
                    'html' => $this->renderAjax('@template/modules/cms/content-element/_product/lookbook/_lookbook', [
                        'lookbookModel' => $model,
                    ]),
                ];
            }
        }

        return $rr;
    }
}