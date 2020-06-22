<?php

namespace common\widgets\checkout;

use common\helpers\User;
use modules\shopandshow\models\shop\forms\QuickOrder;
use modules\shopandshow\models\shop\ShopBasket;
use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopFuser;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\shopCheckout\ShopCheckoutWidget;


class CheckoutWidget extends ShopCheckoutWidget
{
    /**
     * @var ShopFuser
     */
    public $shopFuser = null;
    public $orderKfss = null;
    public $hasFreeDelivery = null;

    public function run()
    {
        $rr = new RequestResponse();
        $error = '';
        $checkoutStatus = \Yii::$app->request->post('Checkout');
        $checkoutPhone = \Yii::$app->request->post('CheckoutPhone');
        $checkoutEmail = \Yii::$app->request->post('CheckoutEmail');

        if (
            $checkoutStatus === 'finish' &&
            (\Yii::$app->request->post() || $rr->isRequestAjaxPost() || $rr->isRequestPjaxPost())
        ) {

            if (User::isGuest()) {
                return false;
            }

            $order = ShopOrder::checkout($checkoutPhone);

            if ($order && $orderUrl = $order->publicUrl) {

                if ($checkoutEmail){
                    \Yii::$app->user->identity->setAttribute('email', $checkoutEmail);
                    \Yii::$app->user->identity->save();
                }

                \Yii::$app->getResponse()->redirect($orderUrl);
            } else {
                \Yii::error('Не получилось создать заказ 1', 'checkoutLog');
            }
        }

        return $this->render($this->viewFile, [
            'error' => $error,
        ]);
    }

    /**
     * @param $phone
     * @param array $products
     * @param int $quantity
     * @return bool|ShopOrder|string
     */
    public static function oneClick($phone, array $products, $quantity = 1)
    {

        if (User::isGuest()) {
            $user = new QuickOrder();
            $user->phone = $phone;

            if ($registeredUser = $user->signup()) {
                \Yii::$app->user->login($registeredUser, DAYS_30);
            } else {
                \Yii::error('Проблема в  oneClick 1 ' . var_export($user->getErrors()), 'checkoutLog');
            }
        }

        foreach ($products as $product) {
            ShopBasket::addProductOneClick($product, $quantity);
        }

        return ShopOrder::checkout($phone, ShopBasket::TYPE_ONE_CLICK);
    }
}