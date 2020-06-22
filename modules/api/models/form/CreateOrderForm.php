<?php

namespace modules\api\models\form;


use common\helpers\Order;
use common\lists\Contents;
use common\models\user\User;
use modules\shopandshow\models\shop\ShopBuyer;
use modules\shopandshow\models\shop\ShopFuser;
use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopOrderStatus;
use modules\shopandshow\models\shop\ShopProduct;
use modules\shopandshow\models\users\UserEmail;


/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 05/02/2019
 * Time: 15:19
 */
class CreateOrderForm extends \yii\base\Model
{

    public $product_id;
    public $phone;
    public $email;
    public $name;

    public $items;

    public $_order;

    public function rules()
    {
        return [
            [['phone', 'name', 'items'], 'required'],
        ];
    }

    /**
     * @return bool
     */
    public function save()
    {


        //* СОЗДАНИЕ ЗАКАЗА и тп *//

        //Создание пользователя


        $userModel = new \common\models\user\authorizations\SignupForm();
        $userModel->setScenario(User::SCENARIO_RIGISTRATION_FROM_FAST_ORDER);

        $userModel->setAttributes([
            'email' => $this->email,
            'username' => $this->email,
            'password' => 'password',
            'name' => $this->name ?: 'Заказ из мобильного приложения',
            'surname' => '',
            'patronymic' => '',
            'isSubscribe' => true,
            'phone' => $this->phone,
            'bitrix_id' => null,
            'guid' => null,
            'source' => UserEmail::SOURCE_CPA,
            'source_detail' => UserEmail::SOURCE_DETAIL_CPA_KMA,
        ], false);

        $user = $userModel->signup();

        $shopBuyer = new ShopBuyer([
            'shop_person_type_id' => 1,
            'cms_user_id' => $user->id
        ]);

        if (!$shopBuyer) {
            $errText = "[MobApp] Создание заказа. Ошибка при создании профиля пользователя";
            \Yii::error($errText, 'order');
        }
        $shopBuyer->save();

        /**
         * @var $shopFuser ShopFuser
         */

        $shopFuser = new \modules\shopandshow\models\shop\ShopFuser([
            //'person_type_id' => (int)$bitrixOrder['person_type_id'],
            'buyer_id' => $shopBuyer->id,
            'user_id' => $user->id
        ]);
        $shopFuser->loadDefaultValues();

        $shopFuser->save();

        \Yii::$app->shop->setShopFuser($shopFuser);

        $order = ShopOrder::createByFuser($shopFuser);
//        $order->setStatus(ShopOrderStatus::STATUS_CANCELED);
        $order->source = ShopOrder::SOURCE_CPA;
        $order->source_detail = ShopOrder::SOURCE_DETAIL_CPA_KMA;
        $order->save();
        $this->_order = $order;

        $kfssOrderId = '---';

        if ($order) {


            foreach ($this->items as $item) {
                $cmsContentElement = Contents::getContentElementById($item['id']);
                $product = $cmsContentElement->product;
                $shopProduct = ShopProduct::getInstanceByContentElement($cmsContentElement);

                if ($cmsContentElement) {
                    $basket = new \modules\shopandshow\models\shop\ShopBasket();
                    $basket->setAttributes([
                        'order_id' => $order->id,
                        'product_id' => $cmsContentElement->id,
                        'quantity' => $item['quantity'],
                        'name' => $cmsContentElement['name'],
                        'price' => $shopProduct->basePrice(),
                        'currency_code' => 'RUB',
                        'site_id' => $order->site_id,
                        //'discount_price' => $position['DISCOUNT_PRICE'],
                        'has_removed' => 0,
                        //'discount_name' => $position['DISCOUNT_NAME'],
                        //'discount_value' => $position['DISCOUNT_VALUE'],
                        'main_product_id' => $product->product ? $product->product->id : $product->id
                    ]);

                    if (!$basket->save()) {
                        $errText = "[MobApp] Создание заказа. Ошибка при сохранении элемента корзины. Error:" . var_export($basket->errors, true);
                        \Yii::error($errText, 'order');
                    }
                } else {
                    $errText = "[MobApp] Создание заказа. Продукт не найден. ID='{$this->product_id}'";
                    \Yii::error($errText, 'order');
                }
            }

//            $orderKfss = \Yii::$app->kfssApi->createOrder();

            $orderKfss = \Yii::$app->kfssApiV2->create(Order::getCreateData());


            if ($orderKfss && !empty($orderKfss['orderId'])) {
                $order->order_number = $orderKfss['orderId'];
                $order->setStatus(ShopOrderStatus::STATUS_SUCCESS);
                $order->save(false);
                $responsePosition = \Yii::$app->kfssApiV2->setPosition(Order::getPosition([$basket]), $order->order_number);
                $responseComplete = \Yii::$app->kfssApiV2->checkoutComplete(Order::getCheckoutCompleteData($order));
                $kfssOrderId = $orderKfss['orderId'];
                $shopFuser->external_order_id = $kfssOrderId;
                $shopFuser->save();

//                $addCouponResponse = \Yii::$app->kfssApi->addCoupon(self::COUPON_MOBILE_INSTALL_CODE);
//                $orderKfss = \Yii::$app->kfssApi->updateOrder();

                //Приводим корзину в соответствие с заказом из АПИ
                \Yii::$app->kfssApiV2->recalculateOrder($orderKfss);

            } else {
                //Не удалось создать заказ
                \Yii::error('Empty KFSS order id for fuser #' . $shopFuser->id);
            }

        } else {
            $errText = "[MobApp] Создание заказа. Не могу привязывать товары, не создался заказ";
            \Yii::error($errText, 'order');
        }


        return true;
    }

    public function getOrder()
    {
        return $this->_order;
    }

}