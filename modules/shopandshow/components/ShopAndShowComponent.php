<?php

namespace modules\shopandshow\components;

use common\helpers\Strings;
use common\lists\Contents;
use common\models\user\authorizations\SignupForm;
use common\models\user\User;
use modules\shopandshow\components\amqp\SSMessageBus;
use modules\shopandshow\models\shop\ShopBasket;
use modules\shopandshow\models\shop\ShopDiscount;
use modules\shopandshow\models\shop\ShopDiscountCoupon;
use modules\shopandshow\models\shop\ShopFuser;
use modules\shopandshow\models\shop\ShopOrder;
use skeeks\cms\components\Cms;
use yii\base\Component;
use yii\helpers\Json;

/**
 * Class ShopAndShowComponent
 * @package modules\shopandshow\components
 */
class ShopAndShowComponent extends Component
{


    /**
     * Отправить заказ в системы KFSS, 1c
     * @param ShopOrder $order
     * @return mixed
     */
    public function sendCreateOrder(ShopOrder $order)
    {

        $channelSale = \Yii::$app->shopAndShowSettings->channelSaleGuid;

        /**
         * @var SSMessageBus $queue
         */
        $queue = clone \Yii::$app->siteExchange; //  testExchange

        $queue->vhost = '/';
        $queue->routingKey = sprintf('%s.%s.%s', 'SITE', 'order', 'create');
        $queue->exchangeName = 'OrderMainExchange';

        $info = [
            "Type" => "CL_ORDER",
            "Version" => "3.0", //Версия сообщения (string)
            "Source" => 'SITE', //Источник сообщения (string)
            "SourceDetail" => SS_SITE . '_' . YII_ENV . '_' . $channelSale, //Детализация источника (string)
            "Date" => date('Y-m-d H:i:s'), // "2017-08-08T10:00:00+03:00" //дата и время отправки сообщения в очередь
        ];

        $data = [
            'Info' => $info,
            'Data' => $this->getExportOrder($order),
        ];

        $queue->push($data);

        $queueLog = new \console\controllers\queues\QueueLog([
            'component' => self::className(),
            'exchange_name' => $queue->exchangeName,
            'queue_name' => $queue->queueName,
            'routing_key' => $queue->routingKey,
            'job_class' => null,
            'status' => \console\controllers\queues\QueueLog::STATUS_PUSHED,
            'message' => Json::encode($data)
        ]);

        $queueLog->save(false);

        return true;
    }

    /**
     * Отправить позиции заказа в системы KFSS, 1c
     * @param ShopOrder $order
     * @return mixed
     */
    public function sendCreateOrderPositions(ShopOrder $order)
    {

        $channelSale = \Yii::$app->shopAndShowSettings->channelSaleGuid;

        /**
         * @var SSMessageBus $queue
         */
        $queue = clone \Yii::$app->siteExchange; // siteExchange testExchange

        $queue->vhost = '/';
        $queue->routingKey = sprintf('%s.%s.%s', 'SITE', 'order_pos', 'create');
        $queue->exchangeName = 'OrderPosExchange';

        $info = [
            "Type" => "CL_ORDER_POS",
            "Version" => "3.0", //Версия сообщения (string)
            "Source" => 'SITE', //Источник сообщения (string)
            "SourceDetail" => SS_SITE . '_' . YII_ENV . '_' . $channelSale, //Детализация источника (string)
            "Date" => date('Y-m-d H:i:s'), // "2017-08-08T10:00:00+03:00" //дата и время отправки сообщения в очередь
        ];

        $data = [
            'Info' => $info,
            'Data' => $this->getExportOrderPositions($order),
        ];

        $queue->push($data);

        $queueLog = new \console\controllers\queues\QueueLog([
            'component' => self::className(),
            'exchange_name' => $queue->exchangeName,
            'queue_name' => $queue->queueName,
            'routing_key' => $queue->routingKey,
            'job_class' => null,
            'status' => \console\controllers\queues\QueueLog::STATUS_PUSHED,
            'message' => Json::encode($data)
        ]);

        $queueLog->save(false);

        return true;
    }

    /**
     * @param ShopOrder $order
     * @return array
     */
    public function getExportOrder(ShopOrder $order)
    {
        $user = $order->user;
        $noDataLabel = '';

        $UserName = $user->displayName;
        $UserSurname = $user->relatedPropertiesModel->getAttribute('LAST_NAME');
        $UserPatronymic = $user->relatedPropertiesModel->getAttribute('PATRONYMIC');

        $UserFullName = join(' ', array_filter([$UserSurname, $UserName, $UserPatronymic]));

        $shopFuser = ShopFuser::getInstanceByUser($user);
        $profile = $shopFuser->getProfileParams();

        return [
            'OrderGuid' => $order->guid->getGuid(),
            'BPGuid' => '5E7BA91651501219E0538201090ACBAD',
            'ChannelGuid' => \Yii::$app->shopAndShowSettings->channelSaleGuid,
            'OrderNumber' => $order->order_number ?: $order->id,
            'OrderNumberKFSS' => $noDataLabel,
            'SpecCode' => $noDataLabel, // //Специальный код, например код "Ш"
            'CreateDate' => date('Y-m-d H:i:s', $order->created_at),
            'ClientGuid' => $user->guid->getGuid(),
            'ClientName' => $UserFullName,
            'ClientEmail' => $user->email,
            'ClientID' => $user->bitrix_id,
            'Origin' => 'NEW_SITE', // Откуда пришел заказ
            'OriginalSum' => (float)$order->price + (float)$order->price_delivery + (float)$order->discount_value, //Цена заказа с доставкой без скидок
            'Sum' => (float)$order->price + (float)$order->price_delivery, //Цена заказа с доставкой с учетом всех скидок
            'Payment' => [[
                'Guid' => '1F27F29843644AE788465C35711CC8AF', // 1F27F29843644AE788465C35711CC8AF NALPAY Наложенным платежом
                'Sum' => 0
            ]],
            'PhoneCID' => $noDataLabel,
            'PhoneMain' => \common\helpers\Strings::onlyInt($user->phone),
            'PhoneExt' => (string)$order->buyer->relatedPropertiesModel->getAttribute('phone'),
            'Operator' => $noDataLabel,
            'Comment' => $order->user_description ?: $noDataLabel,
            'Address' => [
                "DisplayName" => join(', ', array_filter([$profile['city'], $profile['address']])), //Представление
                "ZIP" => $profile['postal_code'], //Индекс
                "Country" => 'Россия', //Страна
                "Region" => $profile['Region'], //Регион
                "District" => $profile['District'], //Район
                "SettlementType" => $profile['SettlementType'], //Тип местности
                "SettlementName" => $profile['SettlementName'], //Название местности
                "StreetName" => $profile['StreetName'], //Название улицы
                "StreetNumber" => $profile['StreetNumber'], //Номер дома
                "BuildNumber" => $profile['BuildNumber'], //Номер строения
                "DoorNumber" => $profile['DoorNumber'], //Номер квартиры
                "FiasCodeProvince" => $profile['FiasCodeProvince'], //Кладр области
                "FiasCodeDistrict" => $profile['FiasCodeDistrict'], //Кладр района
                "FiasCodeCity" => $profile['FiasCodeCity'], // Кладр города
                "FiasCodeStreet" => $profile['FiasCodeStreet'], //Кладр улицы
                "FiasCodeBuilding" => $profile['FiasCodeBuilding'], //Кладр дома
            ],
            'Discount' =>
                $order->discount_value > 0
                    ? [
                        [
                            'Guid' => $noDataLabel,
                            'Sum' => $order->discount_value,
                        ]
                      ]
                    : [],
            'Delivery' => [
                'Guid' => 'A6725CE0ED8B45589E1C8F3A0BE358A2', // Тип доставки не определен - (KFSS_ID = 460)
                'CarrierGuid' => '649EDACA745BA0E8E0534401090AE2D4', // ООО "Бета ПРО" - (KFSS_ID = 10)
                'MailGuid' => $noDataLabel,
                'OriginalPrice' => 0,
                'Price' => 0,
                'Discount' => [],
            ],
        ];
    }

    /**
     * @param ShopOrder $order
     * @return array
     */
    public function getExportOrderPositions(ShopOrder $order)
    {
        $noDataLabel = '';
        $promoCodeGuidSite = '691960D2CAF624DBE0538201090A48AA';
        $promoCodeGuidCoupon = '691960D2CAF824DBE0538201090A48AA';

        $discountCoupons = $order->discountCoupons;

        $goods = [];

        foreach ($order->shopBaskets as $shopBasket) {

            $product = Contents::getContentElementById($shopBasket->product_id);

            $lotGuid = ($product && $product->product) ? $product->product->guid->getGuid() : '';
            $baseModificationGuid = null;
            if ($product && $product->isLot()) {
                $baseModificationGuid = $product->relatedPropertiesModel->getAttribute('base_modification_guid');
            }
            $modificationGuid = ($product && $product->isOffer()) ? $product->guid->getGuid() : $baseModificationGuid;

            $item = [
                'LotGuid' => $lotGuid,
                'ModificationGuid' => $modificationGuid,
                'OriginalPrice' => ((float)$shopBasket->price + (float)$shopBasket->discount_price),
                'Price' => (float)$shopBasket->price,
                'Quantity' => (int)$shopBasket->quantity,
                'OriginalSum' => $shopBasket->price * $shopBasket->quantity + (float)$shopBasket->discount_price,
                'Sum' => $shopBasket->price * $shopBasket->quantity,
                'Discount' => []
            ];

            if ($shopBasket->moneyDiscount->getValue() > 0) {
                $item['Discount'][] = [
                    'Guid' => $noDataLabel,
                    'Sum' => $shopBasket->moneyDiscount->getValue()
                ];
            }

            $goods[] = $item;
        }

        $orderData = [
            'OrderGuid' => $order->guid->getGuid(),
            'BPGuid' => '5E7BA91651501219E0538201090ACBAD',
            'ChannelGuid' => \Yii::$app->shopAndShowSettings->channelSaleGuid,
            'NeedCallClient' => true,
            'Promo' => [],
            'Goods' => $goods
        ];

        if ($discountCoupons) {
            /** @var ShopDiscountCoupon $discountCoupon */
            foreach ((array)$discountCoupons as $discountCoupon) {
                $orderData['Promo'][] = [
                    'TypeGuid' => (!empty($discountCoupon->shopDiscount) && $discountCoupon->shopDiscount->code == ShopDiscount::DISCOUNT_CODE_500RUB)
                        ? $promoCodeGuidCoupon : $promoCodeGuidSite,
                    'Code' => $discountCoupon->coupon
                ];
            }
        }

        return $orderData;
    }



    /**
     * Отправить пользователя
     * @param $user
     * @return void
     */
    public function sendCreateUser($user)
    {
        $channelSale = \Yii::$app->shopAndShowSettings->channelSaleGuid;


        return;


        /**
         * @var SSMessageBus $queue
         */
        $queue = clone \Yii::$app->siteExchange;

        $queue->routingKey = SS_SITE;

        $queue->exchangeName = 'UserExchange';

        $info = [
            "Type" => "User",
            "Version" => "1.0", //Версия сообщения (string)
            "Source" => SS_SITE, //Источник сообщения (string)
            "SourceDetail" => SS_SITE . '_' . YII_ENV . '_' . $channelSale, //Детализация источника (string)
            "Date" => date('Y-m-d H:i:s'), // "2017-08-08T10:00:00+03:00" //дата и время отправки сообщения в очередь
        ];

        $data = [
            'Info' => $info,
            'Data' => $this->getExportUser($user),
        ];

        $queue->push($data);
    }


    /**
     * @param User $user
     * @return array
     */
    public function getExportUser($user)
    {
        $login = $user->phone;

        if (!$login || ($login && strlen($login) <= 3)) {
            $login .= '_' . date('YmdH');
        }

        $login .= '_new_site_' . date('YmdHi');

        return [
            'Guid' => $user->guid->getGuid(),
            'Active' => $user->active == Cms::BOOL_Y ? true : false,
            'FirstName' => $user->name,
            'LastName' => $user->relatedPropertiesModel->getSmartAttribute('LAST_NAME'),
            'SecondName' => $user->relatedPropertiesModel->getSmartAttribute('PATRONYMIC'),
            'Login' => $login,
            'Phone' => $user->phone,
            'Email' => $user->email,
        ];

    }























    // **************************************БИТРИКС ТЕМА***************************************************************

    /**
     * Отправить заказ в битрикс
     * @param ShopOrder $order
     * @param bool $isSendUser
     * @return mixed
     */
    public function sendCreateOrderBitrix(ShopOrder $order, $isSendUser = false)
    {
        if ($isSendUser && false) {
            \Yii::$app->shopAndShow->sendCreateUserBitrix(User::findOne($order->user->id));
        }

        $queue = clone \Yii::$app->siteExchange;

        $queue->queueName = 'website.exchange';
        $queue->routingKey = 'website.exchange.order.create';

        $data = [
            'entity' => 'register-order',
            'data' => $this->getExportOrderBitrix($order),
            'timestamp' => time(),
        ];

//        var_dump(json_encode($data));

//        \Yii::error(json_encode($data));

        return $queue->push($data);
    }

    /**
     * @param ShopOrder $order
     * @return array
     */
    public function getExportOrderBitrix(ShopOrder $order)
    {
        $items = [];
        $itemsString = '';

        // купоны по промокодам
        $coupons = [];
        // купоны подписчика
        $fixedCoupons = [];

        if ($order->discountCoupons) {
            foreach ($order->discountCoupons as $coupon) {
                if ($coupon->shopDiscount->code == ShopDiscount::DISCOUNT_CODE_500RUB) {
                    $fixedCoupons[] = $coupon->coupon;
                }
                else {
                    $coupons[] = $coupon->coupon;
                }
            }
        }

        foreach ($order->shopBaskets as $shopBasket) {
            $item = [
                'id' => $shopBasket->product_id,
                'bitrix_id' => $shopBasket->product->cmsContentElement->bitrix_id,
                'name' => $shopBasket->name,
                'buy_price' => $shopBasket->money->getValue(),
                'original_price' => $shopBasket->moneyOriginal->getValue(),
                'quantity' => $shopBasket->quantity,
            ];

            if ($shopBasket->moneyDiscount->getValue() > 0) {

                $item['discount'] = [
                    'discount_sum' => $shopBasket->moneyDiscount->getValue(),
                    'discount_name' => $shopBasket->discount_name,
                    'discount_coupon' => join(',', $coupons) //$shopBasket->discount_coupon
                ];

            }

            $items[] = $item;

            $itemsString .=
//                $shopBasket->product->cmsContentElement->bitrix_id . " " .
                $shopBasket->name . " " .
                $shopBasket->quantity . "шт " .
                $shopBasket->money->getValue() . 'руб => ' .
                ($shopBasket->quantity * $shopBasket->money->getValue()) . 'руб' .
                "\n";

        }

        return [
            'id' => $order->id,
            'guid' => $order->guid->getGuid(),

            'key' => $order->key,
            'external_user_id' => $order->created_by,
            'created_date' => date('Y-m-d H:i:s', $order->created_at),

            'order_cost' => $order->price,
            'order_discount' => $order->discount_value,

            'discount_coupons' => $fixedCoupons,

            'items' => $items,

            'user' => array_filter([
                'id' => $order->user->id,
                'guid' => $order->user->guid->getGuid(),
                'email' => $order->user->email,
                'phone' => str_replace(['(', ' ', ')', '+', '-'], '', $order->user->phone),
                'bitrix_id' => $order->user->bitrix_id
            ]),

            'second_phone' => $order->buyer->relatedPropertiesModel->getAttribute('phone'),

            'products_count' => count($items),
            'products_text' => $itemsString,

            'comments' => $order->comments
        ];
    }


    /**
     * Отправить пользователя
     * @param $user
     */
    public function sendCreateUserBitrix($user)
    {
        $queue = clone \Yii::$app->siteExchange;

        $queue->queueName = 'website.exchange';
        $queue->routingKey = 'website.exchange.user.create';

        $data = [
            'entity' => 'register-user',
            'data' => $this->getExportUserBitrix($user),
            'timestamp' => time(),
        ];

        $queue->push($data);
    }


    /**
     * @param User $user
     * @return array
     */
    public function getExportUserBitrix($user)
    {
        $openPassword = $user->relatedPropertiesModel->getSmartAttribute('secretCode');

        if (!$openPassword || ($openPassword && strlen($openPassword) < SignupForm::MIN_PASSWORD_LENGTH)) {
            $openPassword = rand(111111, 9999999);
        }

        $login = Strings::removeSpaces($user->phone);

        if (!$login || ($login && strlen($login) <= 3)) {
            $login .= '_' . date('YmdH');
        }

        $login .= '_new_site_' . date('YmdHi');

        return array(
            'id' => $user->id,
            'guid' => $user->guid->getGuid(),
            'first_name' => $user->name,
            'login' => $login,
            'phone' => $user->phone,
            'email' => $user->email,
            'last_name' => $user->relatedPropertiesModel->getSmartAttribute('LAST_NAME'),
            'open_password' => $openPassword,
            'subscribe_to_newsletter' => $user->relatedPropertiesModel->getSmartAttribute('SUBSCRIBE_TO_NEWSLETTER') == 1 ? true : false,
//            'bitrix_id' => $user->bitrix_id !== null ? (int)$user->bitrix_id : null,
        );

    }

    // **************************************БИТРИКС ТЕМА***************************************************************





    /**
     * Отправить пользователя
     */
    public function sendUpdateUser(User $user)
    {

        $queue = clone \Yii::$app->siteExchange;

        $queue->queueName = 'website.exchange';
        $queue->routingKey = 'website.exchange.user.update';

        $data = [
            'entity' => 'update-user',
            'data' => $this->getExportUser($user),
            'timestamp' => time(),
        ];

        $queue->push($data);
    }

    public function sendCreateOrderRaw(ShopOrder $order)
    {
        return true;

        return \Yii::$app->rawExchange->push(json_encode($this->getExportOrderBitrix($order)), 'website.order.create');
    }


    /**
     * Отправка брошенных корзин в очередь
     * @param array $row
     * @return bool
     */
    public function sendAbandonedBaskets(array $row)
    {
        /**
         * @var SSMessageBus $queue
         */
        $queue = clone \Yii::$app->siteExchange;

        $queue->routingKey = sprintf('%s.%s.%s', SS_SITE, 'abandoned_baskets', 'create');
        $queue->vhost = '/';
        $queue->exchangeName = 'SiteCallExchange';

        $info = [
            "Type" => "OUT_CALL_WITH_BASKET",
            "Version" => "2.0", //Версия сообщения (string)
            "Source" => SS_SITE, //Источник сообщения (string)
            "SourceDetail" => SS_SITE . '_' . YII_ENV, //Детализация источника (string)
            "Date" => date('Y-m-d H:i:s'), // "2017-08-08T10:00:00+03:00" //дата и время отправки сообщения в очередь
        ];

        $baskets = $this->getAbandonedBasketsFromArray($row);
        if (!$baskets) {
            return false;
        }

        $data = [
            'Info' => $info,
            'Data' => $baskets,
        ];

        $queue->push($data);

        $queueLog = new \console\controllers\queues\QueueLog([
            'component' => self::className(),
            'exchange_name' => $queue->exchangeName,
            'queue_name' => $queue->queueName,
            'routing_key' => $queue->routingKey,
            'job_class' => null,
            'status' => \console\controllers\queues\QueueLog::STATUS_PUSHED,
            'message' => Json::encode($data)
        ]);

        $queueLog->save(false);

        return true;
    }

    /**
     * @param array $row
     * @return array|bool
     */
    protected function getAbandonedBasketsFromArray(array $row)
    {
        $goods = [];

        /** @var ShopBasket[] $shopBaskets */
        $shopBaskets = ShopBasket::find()->innerJoinWith('cmsContentElement')->where("shop_basket.id IN ({$row['BASKETS']})")->all();

        foreach ($shopBaskets as $shopBasket) {
            $cmsContentElement = $shopBasket->cmsContentElement;

            $lotGuid = ($cmsContentElement && $cmsContentElement->product) ? $cmsContentElement->product->guid->getGuid() : '';
            $modificationGuid = ($cmsContentElement && $cmsContentElement->isOffer()) ? $cmsContentElement->guid->getGuid() : $lotGuid;

            // не все модификации прогуижены
            if (!$modificationGuid) {
                continue;
            }

            $item = [
                'ModificationGuid' => $modificationGuid,
                'Quantity' => $shopBasket->quantity,
            ];
            $goods[] = $item;
        }

        if (!$goods) {
            return false;
        }

        $data  = [
            'PhoneNumber' => $row['PROP_PHONE'],
            'Goods' => $goods,
        ];

        return $data;
    }

    public function sendCallBack(array $row)
    {
        /**
         * @var SSMessageBus $queue
         */
        $queue = clone \Yii::$app->siteExchange;

        $queue->routingKey = sprintf('%s.%s.%s', SS_SITE, 'out_call', 'create');
        $queue->vhost = '/';
        $queue->exchangeName = 'SiteCallExchange';

        $info = [
            "Type" => "OUT_CALL",
            "Version" => "2.0", //Версия сообщения (string)
            "Source" => SS_SITE, //Источник сообщения (string)
            "SourceDetail" => SS_SITE . '_' . YII_ENV, //Детализация источника (string)
            "Date" => date('Y-m-d H:i:s'), // "2017-08-08T10:00:00+03:00" //дата и время отправки сообщения в очередь
        ];

        $data = [
            'Info' => $info,
            'Data' => [
                'PhoneNumber' => $row['phone'],
                'ClientName' => $row['name'],
                // TimeZone
                // AllowedTimeFrom
                // AllowedTimeTo
            ],
        ];

        $queue->push($data);

        $queueLog = new \console\controllers\queues\QueueLog([
            'component' => self::className(),
            'exchange_name' => $queue->exchangeName,
            'queue_name' => $queue->queueName,
            'routing_key' => $queue->routingKey,
            'job_class' => null,
            'status' => \console\controllers\queues\QueueLog::STATUS_PUSHED,
            'message' => Json::encode($data)
        ]);

        $queueLog->save(false);

        return true;
    }
}
