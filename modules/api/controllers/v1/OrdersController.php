<?php

/**
 * /api/v1/orders/
 * /api/v1/orders/coupon
 */

namespace modules\api\controllers\v1;

use common\helpers\ArrayHelper;
use common\helpers\Msg;
use common\lists\Contents;
use modules\api\controllers\ActiveController;
use modules\shopandshow\models\shop\ShopBuyer;
use modules\shopandshow\models\shop\ShopDiscount;
use modules\shopandshow\models\shop\ShopDiscountCoupon;
use modules\shopandshow\models\shop\ShopFuser;
use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopOrderStatus;
use modules\shopandshow\models\shop\ShopProduct;
use modules\shopandshow\models\users\UserEmail;
use common\models\user\User AS UserModel;

class OrdersController extends ActiveController
{

    /**
     * Купон для мобильного приложения с 10% скидкой
     */
    const COUPON_MOBILE_INSTALL_CODE = 'mobile-order';

    public function verbs()
    {
        $verbs = [
            'index' => ['GET', 'POST'],
            'coupon' => ['GET'],
        ];

        return ArrayHelper::merge(parent::verbs(), $verbs);
    }


    public function actionCoupon()
    {

        $coupon = (string)$this->request->get('coupon');

        if (!$coupon) {
            return [];
        }

        /**
         * @var $coupon ShopDiscountCoupon
         */
        $coupon = ShopDiscountCoupon::find()
            ->andWhere(['coupon' => $coupon])
            ->andWhere(['is_active' => 1])
            ->andWhere(['OR', ['<=', 'active_from', time()], ['active_from' => null]])
            ->andWhere(['OR', ['>=', 'active_to', time()], ['active_to' => null]])
            ->one();

        if (!$coupon) {
            return [];
        }

        $discountType = $coupon->shopDiscount->value_type == ShopDiscount::VALUE_TYPE_P ?
            'percent' : $coupon->shopDiscount->value_type == ShopDiscount::VALUE_TYPE_F ? 'fixed_cart' : 'fixed_product'; // percent, fixed_cart and fixed_product. Default is fixed_cart.

        if ($coupon) {
            return [[
                'id' => $coupon->id,
                'code' => $coupon->coupon,
                'amount' => (int)$coupon->shopDiscount->value,
                'date_created' => date('Y-m-d H:i:s', $coupon->created_at),
                'date_created_gmt' => $coupon->created_at,
                'date_modified' => date('Y-m-d H:i:s', $coupon->updated_at),
                'date_modified_gmt' => $coupon->updated_at,
                'discount_type' => $discountType,
                'description' => '',
                'date_expires' => null, //date('Y-m-d H:i:s', $coupon->active_to),
                'date_expires_gmt' => null, //$coupon->active_to,
                'usage_count' => $coupon->use_count,
                'individual_use' => true,
                'product_ids' => [],
                'excluded_product_ids' => [],
                'usage_limit' => null,
                'usage_limit_per_user' => null,
                'limit_usage_to_x_items' => null,
                'free_shipping' => false,
                'product_categories' => [],
                'excluded_product_categories' => [],
                'exclude_sale_items' => false,
                'minimum_amount' => '0.00',
                'maximum_amount' => '0.00',
                'email_restrictions' => [],
                'used_by' => [],
                'meta_data' => [],
            ]];
        }

        return [];
    }


    public function actionIndex()
    {
        if (\Yii::$app->request->isPost) {
            return $this->createOrder();
        }

        return ['status' => 'ok'];
    }

    public function createOrder()
    {

        //Формат запроса для примера
        $requestDataTest = [
            'payment_method' => 'bacs',
            'payment_method_title' => 'Direct Bank Transfer',
            'set_paid' => false,
            'billing' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'address_1' => '969 Market',
                'address_2' => '',
                'city' => 'San Francisco',
                'state' => 'CA',
                'postcode' => '94103',
                'country' => 'US',
                'email' => 'john.doe@example.com',
                'phone' => '(926) 555-5555'
            ],
            'shipping' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'address_1' => '969 Market',
                'address_2' => '',
                'city' => 'San Francisco',
                'state' => 'CA',
                'postcode' => '94103',
                'country' => 'US'
            ],
            'line_items' => [
//                [
//                    'product_id' => 351341,
//                    'quantity' => 1
//                ],
                [
                    'product_id' => 351342,
                    'variation_id' => 0,
                    'quantity' => 2
                ]
            ],
            'shipping_lines' => [
                [
                    'method_id' => 'flat_rate',
                    'method_title' => 'Flat Rate',
                    'total' => 10
                ]
            ]
        ];

        $requestData = \yii\helpers\Json::decode(\Yii::$app->request->rawBody);

//        \Yii::error("[MobApp] rawBody decoded: " . var_export($requestData, true));

        //Request data

        //TODO $requestDataTest - только для тестов! Удалить на бою!!!!!!!!!!!!!!!.
        $appBilling = ArrayHelper::getValue($requestData, 'billing', $requestDataTest['billing']);
        $appShipping = ArrayHelper::getValue($requestData, 'shipping', $requestDataTest['shipping']);
        $products = ArrayHelper::getValue($requestData, 'line_items', $requestDataTest['line_items']);


        //Response data

        $billing = [
            "first_name" => "John",
            "last_name" => "Doe",
            "company" => "",
            "address_1" => "969 Market",
            "address_2" => "",
            "city" => "San Francisco",
            "state" => "CA",
            "postcode" => "94103",
            "country" => "US",
            "email" => "john.doe@example.com",
            "phone" => "(926) 555-5555"
        ];

        $shipping = [
            "first_name" => "John",
            "last_name" => "Doe",
            "company" => "",
            "address_1" => "969 Market",
            "address_2" => "",
            "city" => "San Francisco",
            "state" => "CA",
            "postcode" => "94103",
            "country" => "US"
        ];

        //* СОЗДАНИЕ ЗАКАЗА и тп *//

        //Создание пользователя

        $lastName = ArrayHelper::getValue($appBilling, 'last_name');
        $firstName = ArrayHelper::getValue($appBilling, 'first_name');
        $email = ArrayHelper::getValue($appBilling, 'email');
        $phone = ArrayHelper::getValue($appBilling, 'phone');

        $name = trim($lastName . ' ' . $firstName);
        $login = $email;

        $userModel = new \common\models\user\authorizations\SignupForm();
        $userModel->setScenario(UserModel::SCENARIO_RIGISTRATION_FROM_FAST_ORDER);

        $userModel->setAttributes([
            'email' => $email,
            'username' => $login,
            'password' => 'password',
            'name' => $name ?: 'Заказ из мобильного приложения',
            'surname' => '',
            'patronymic' => '',
            'isSubscribe' => true,
            'phone' => $phone,
            'bitrix_id' => null,
            'guid' => null,
            'source' => UserEmail::SOURCE_MOBILE_APP,
            'source_detail' => UserEmail::SOURCE_DETAIL_CHECK_ORDER_MOBILE_APP,
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
        $order->source = ShopOrder::SOURCE_MOBILE_APP;
        $order->source_detail = ShopOrder::SOURCE_DETAIL_FAST_ORDER_MOBILE_APP;
        $order->save();

        $kfssOrderId = '---';

        if ($order) {
            foreach ($products as $cartProduct) {

                $productId = $cartProduct['product_id'];
                $quantity = $cartProduct['quantity'];

                if (isset($cartProduct['variation_id']) && $cartProduct['variation_id']) {
                    $cmsContentElement = Contents::getContentElementById($cartProduct['variation_id']);
                } else {
                    $cmsContentElement = Contents::getContentElementById($productId);
                }


                $product = $cmsContentElement->product;
                $shopProduct = ShopProduct::getInstanceByContentElement($cmsContentElement);

                if ($cmsContentElement) {
                    $basket = new \modules\shopandshow\models\shop\ShopBasket();
                    $basket->setAttributes([
                        'order_id' => $order->id,
                        'product_id' => $cmsContentElement->id,
                        'quantity' => $quantity < 1 ? 1 : $quantity,
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
                    $errText = "[MobApp] Создание заказа. Продукт не найден. ID='{$productId}'";
                    \Yii::error($errText, 'order');
                }
            }

            $orderKfss = \Yii::$app->kfssApiV2->createOrder();


            if ($orderKfss && !empty($orderKfss['orderId'])) {
                $kfssOrderId = $orderKfss['orderId'];
                $shopFuser->external_order_id = $kfssOrderId;
                $shopFuser->save();

//                $addCouponResponse = \Yii::$app->kfssApi->addCoupon(self::COUPON_MOBILE_INSTALL_CODE);
//                $orderKfss = \Yii::$app->kfssApi->updateOrder();

                //Приводим корзину в соответствие с заказом из АПИ
                \Yii::$app->kfssApiV2->recalculateOrder($orderKfss);

            } else {
                //Не удалось создать заказ
                \Yii::error('Empty KFSS order id for fuser #'. $shopFuser->id);
            }

        } else {
            $errText = "[MobApp] Создание заказа. Не могу привязывать товары, не создался заказ";
            \Yii::error($errText, 'order');
        }

        //* /СОЗДАНИЕ ЗАКАЗА и тп *//

        //Формат ответа для примера
        $result =
            [
                "id" => $order->id,
                "kfssId" => $kfssOrderId,
                "parent_id" => 0,
                "number" => $order->id,
                "order_key" => "",
                "created_via" => "rest-api",
                "version" => "1.0.0",
                "status" => ShopOrderStatus::STATUS_CANCELED,
                "currency" => "RUB",
                "date_created" => date("Y-m-d H:i:s", $order->created_at),
                "date_created_gmt" => date("Y-m-d H:i:s", $order->created_at),
                "date_modified" => date("Y-m-d H:i:s"),
                "date_modified_gmt" => date("Y-m-d H:i:s"),
                "discount_total" => "0.00",
                "discount_tax" => "0.00",
                "shipping_total" => "0.00",
                "shipping_tax" => "0.00",
                "cart_tax" => "1.35",
                "total" => $shopFuser->money,
                "total_tax" => "1.35",
                "prices_include_tax" => false,
                "customer_id" => 0,
                "customer_ip_address" => "",
                "customer_user_agent" => "",
                "customer_note" => "",
                "billing" => $appBilling,
                "shipping" => $appShipping,
                "payment_method" => "bacs",
                "payment_method_title" => "Direct Bank Transfer",
                "transaction_id" => "",
                "date_paid" => null,
                "date_paid_gmt" => null,
                "date_completed" => null,
                "date_completed_gmt" => null,
                "cart_hash" => "",
                "meta_data" => [
                    [
                        "id" => 13106,
                        "key" => "_download_permissions_granted",
                        "value" => "yes"
                    ]
                ],
                "line_items" => [
                    [
                        "id" => 315,
                        "name" => "Woo Single #1",
                        "product_id" => 93,
                        "variation_id" => 0,
                        "quantity" => 2,
                        "tax_class" => "",
                        "subtotal" => "6.00",
                        "subtotal_tax" => "0.45",
                        "total" => "6.00",
                        "total_tax" => "0.45",
                        "taxes" => [
                            [
                                "id" => 75,
                                "total" => "0.45",
                                "subtotal" => "0.45"
                            ]
                        ],
                        "meta_data" => [],
                        "sku" => "",
                        "price" => 3
                    ],
                    [
                        "id" => 316,
                        "name" => "Ship Your Idea &ndash; Color => Black, Size => M Test",
                        "product_id" => 22,
                        "variation_id" => 23,
                        "quantity" => 1,
                        "tax_class" => "",
                        "subtotal" => "12.00",
                        "subtotal_tax" => "0.90",
                        "total" => "12.00",
                        "total_tax" => "0.90",
                        "taxes" => [
                            [
                                "id" => 75,
                                "total" => "0.9",
                                "subtotal" => "0.9"
                            ]
                        ],
                        "meta_data" => [
                            [
                                "id" => 2095,
                                "key" => "pa_color",
                                "value" => "black"
                            ],
                            [
                                "id" => 2096,
                                "key" => "size",
                                "value" => "M Test"
                            ]
                        ],
                        "sku" => "Bar3",
                        "price" => 12
                    ]
                ],
                "tax_lines" => [
                    [
                        "id" => 318,
                        "rate_code" => "US-CA-STATE TAX",
                        "rate_id" => 75,
                        "label" => "State Tax",
                        "compound" => false,
                        "tax_total" => "1.35",
                        "shipping_tax_total" => "0.00",
                        "meta_data" => []
                    ]
                ],
                "shipping_lines" => [
                    [
                        "id" => 317,
                        "method_title" => "Flat Rate",
                        "method_id" => "flat_rate",
                        "total" => "10.00",
                        "total_tax" => "0.00",
                        "taxes" => [],
                        "meta_data" => []
                    ]
                ],
                "fee_lines" => [],
                "coupon_lines" => [],
                "refunds" => [],
                "_links" => [
                    "self" => [
                        [
                            "href" => "https://example.com/wp-json/wc/v2/orders/727"
                        ]
                    ],
                    "collection" => [
                        [
                            "href" => "https://example.com/wp-json/wc/v2/orders"
                        ]
                    ]
                ]
            ];

        return $result;
    }
}