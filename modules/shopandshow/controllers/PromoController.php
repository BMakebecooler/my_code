<?php

namespace modules\shopandshow\controllers;

use common\helpers\ArrayHelper;
use common\helpers\CacheHelper;
use common\lists\TreeList;
use Exception;
use modules\shopandshow\lists\Discount;
use modules\shopandshow\models\common\form\Form2FormProperty;
use modules\shopandshow\models\form\Form2FormSend;
use modules\shopandshow\models\shares\badges\SsBadge;
use modules\shopandshow\models\shares\badges\SsBadgeProduct;
use modules\shopandshow\models\shop\ShopDiscountCoupon;
use skeeks\cms\base\Controller;
use skeeks\cms\components\Cms;
use skeeks\cms\helpers\RequestResponse;
use skeeks\modules\cms\form2\models\Form2Form;
use skeeks\modules\cms\form2\models\Form2FormSendProperty;
use skeeks\cms\models\StorageFile;
use yii\db\Expression;
use yii\web\Response;

/**
 * Class PromoController
 * @package modules\shopandshow\controllers
 */
class PromoController extends Controller
{

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            [
                'class' => 'yii\filters\PageCache',
                'only' => ['get-catalog-action'],
                'duration' => CacheHelper::CACHE_TIME,
                'variations' => CacheHelper::getPomoVariation(),
                'enabled' => CacheHelper::isEnabled()
            ],
        ]);
    }


    /**
     * Получить баннер для каталога (список товаров)
     * @return RequestResponse
     */
    public function actionGetCatalogAction()
    {
        $rr = new RequestResponse();
        $rr->success = false;

        if ($rr->isRequestAjaxPost()) {
            $treeId = (int)\Yii::$app->request->post('tree-id');

            if ($bannerTree = TreeList::getCatalogBannerById($treeId)) {
                $rr->success = true;
                $rr->data = [
                    'html' => $this->renderAjax(
                        '@site/include/adversting/_catalog_banner', [
                        'image' => $bannerTree['image'],
                        'link' => $bannerTree['link'],
                    ]),
                ];
            }
        }

        return $rr;
    }


    public function actionAddCouponCodeS()
    {

        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {

            $couponCode = \Yii::$app->request->post('coupon_code');
            $email = \Yii::$app->request->post('email');

            $couponCode = trim($couponCode);
            $email = trim($email);

            if ($couponCode) {

                try {

                    $discount_coupons = \Yii::$app->shop->shopFuser->discount_coupons;
                    if (sizeof($discount_coupons) > 0) {
                        throw new Exception('Нельзя использовать более одного промокода');
                    }

                    /* @var $applyShopDiscountCoupon ShopDiscountCoupon */
                    $applyShopDiscountCoupon = ShopDiscountCoupon::getActiveCouponByCode($couponCode);
                    if (!$applyShopDiscountCoupon) {
                        throw new Exception(\Yii::t('skeeks/shop/app', 'Coupon does not exist or is not active'));
                    }

                    $discount_coupons[] = $applyShopDiscountCoupon->id;
                    $discount_coupons = array_unique($discount_coupons);
                    \Yii::$app->shop->shopFuser->discount_coupons = $discount_coupons;

                    \Yii::$app->shop->shopFuser->save();
                    \Yii::$app->shop->shopFuser->recalculate()->save();

//                $rr->data = \Yii::$app->shop->shopFuser->jsonSerialize();
                    $rr->success = true;
                    $rr->message = \Yii::t('skeeks/shop/app', 'Coupon successfully installed');

                } catch (\Exception $e) {
                    $rr->message = $e->getMessage();
                    return $rr;
                }


            } elseif ($email) {

                $code = Discount::getByCode('codes');

                if (!$code) {
                    $rr->message = 'Акция не активна.';
                    return $rr;
                } elseif ($code && !$code->promoCode) {
                    $rr->message = 'Извините, промокод еще не заведен.';
                    return $rr;
                }

                if (Form2FormSendProperty::findOne([
                    'value' => $email,
                ])
                ) {
                    $rr->message = 'Извините, но вы уже подписались на рассылку.';
                    return $rr;
                }

                $formSubscribers = Form2Form::findOne(['code' => 'subscribers']);
                $formSubscribersPropertyEmail = Form2FormProperty::findOne(['form_id' => $formSubscribers->id, 'code' => 'email']);

                $newEmail = Form2FormSend::addData([
                    'data_values' => 'a:1:{s:5:"email";s:0:"";}',
                    'data_labels' => 'a:1:{s:5:"email";s:5:"email";}',
                    'value' => $email,
                    'status' => Form2FormSend::STATUS_NEW,
                    'form_id' => $formSubscribers->id,
                    'property_id' => $formSubscribersPropertyEmail->id,
                ]);

                if ($newEmail) {

                    \Yii::$app->mailer->htmlLayout = false;
                    \Yii::$app->mailer->textLayout = false;

                    $message = \Yii::$app->mailer->compose('@templates/mail/promo/code-s-coupon-code', [
                        'discount' => $code ? $code->promoCode : null
                    ])
                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName])
                        ->setTo($email)
                        ->setSubject('Ваш код С');

                    if ($message->send()) {
                        $rr->success = true;
                        $rr->message = 'Код С отправлен вам на почту.';
                        return $rr;
                    }
                } else {
                    $rr->message = 'Возникла непредвиденная ошибка.';
                    return $rr;
                }
            } else {
                $rr->message = 'Не введены данные.';
                return $rr;
            }

            return $rr;
        } else {
            return $this->goBack();
        }

    }

    /**
     * Получить баннер для каталога (список товаров)
     * @return RequestResponse
     */
    public function actionGetWinnersByPhone()
    {

        $rr = new RequestResponse();
        $rr->success = false;

        if (\Yii::$app->request->isAjax) {

            $winnerCode = \Yii::$app->request->get('winnerCode');

            $digitalAprilWidget = new \common\widgets\promo\april2018\DigitalApril2018();
            $winners = $digitalAprilWidget->getWinnersByPhone($winnerCode);

            if ($winners) {
                $resultHtml = '';
                foreach ($winners as $winner) {
                    $winnerFio = $winner->relatedPropertiesModel->getAttribute('winner_fio');
                    $city = $winner->relatedPropertiesModel->getAttribute('winner_city');
                    $prize = $winner->relatedPropertiesModel->getAttribute('winner_product');

                    $cityHtml = $city ? ", {$city}" : '';

                    $resultHtml .= <<<HTML
                            <div class="item-row">{$winnerFio}{$cityHtml} - приз $prize</div>
HTML;
                }


                $rr->data = ['winners' => $resultHtml];
            } else {
                $rr->message = 'No winners';
            }
            $rr->success = true;
        }

        return $rr;
    }

    /**
     * Получение списка плашек (активных) для товаров
     * @return RequestResponse
     */
    public function actionGetProductsBadges()
    {

        $rr = new RequestResponse();
        $rr->success = false;

        //if ($rr->isRequestAjaxPost()) {
        if (\Yii::$app->request->isAjax) {

            $products = SsBadgeProduct::find()->select(['badge_products.product_id', 'badge.cluster_file AS badge_image_file'])
                ->alias('badge_products')
                ->innerJoin(SsBadge::tableName() . ' AS badges', "badges.id = badge_products.badge_id AND badges.active = '".Cms::BOOL_Y."'")
                ->leftJoin(StorageFile::tableName() . ' AS badge',"badge.id = badges.image_id")
                ->andWhere(['<=', 'badges.begin_datetime', new Expression('UNIX_TIMESTAMP(NOW())')])
                ->andWhere(['>=', 'badges.end_datetime', new Expression('UNIX_TIMESTAMP(NOW())')])
                ->asArray()
                ->all();

            $badges = array();
            //Что бы не выяснять пути к одному и тому же файлу
            $badgesImages = [];
            foreach ($products as $product) {
                $imgPath = '';
                if ($product['badge_image_file']){
                    if ( empty($badgesImages[$product['badge_image_file']]) ){
                        $badgesImages[$product['badge_image_file']] = \Yii::$app->storage->getCluster('local')->publicBaseUrl .'/'. $product['badge_image_file'];
                    }
                    $imgPath = $badgesImages[$product['badge_image_file']];
                }

                $badges[(int)$product['product_id']] = $imgPath;
            }

            if ($badges) {
                $rr->data = ['badges' => $badges];
            } else {
                $rr->message = 'No products';
            }
            $rr->success = true;
        }

        return $rr;
    }

    /**
     * Получение списка товаров для актуальной акции КОД С
     * @return RequestResponse
     */
    public function actionGetCodeSProducts()
    {
        $rr = new RequestResponse();
        $rr->success = false;

        if ($rr->isRequestAjaxPost()) {

            // код акции "код С"
            $codeScode = 'codes';

            $discount = \modules\shopandshow\lists\Discount::getByCode($codeScode);

            $products = $discount->getActiveProductsIdsCodeS();

            $productsProper = array();
            foreach ($products as $productId) {
                $productsProper[] = (int)$productId;
            }

            if ($productsProper) {
                $rr->data = ['products' => $productsProper];
            } else {
                $rr->message = 'No products';
            }
            $rr->success = true;
        }

        return $rr;
    }
}