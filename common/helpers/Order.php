<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 25.01.19
 * Time: 12:52
 */

namespace common\helpers;


use modules\shopandshow\components\task\SendMailGunEmailTaskHandler;
use modules\shopandshow\components\task\SendOrderSmsTaskHandler;
use modules\shopandshow\models\shop\forms\QuickOrder;
use modules\shopandshow\models\shop\ShopBasket;
use modules\shopandshow\models\shop\ShopFuser;
use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopProduct;
use modules\shopandshow\models\task\SsTask;
use yii\helpers\StringHelper;

class Order
{

    public static function sendSmsCreateOrder($order, $text)
    {
        \Yii::info("Add task for sand sms" . 'checkoutLog');
        if ($order->user->isApprovePhone() && !in_array($order->user_id, [1000, 36574, 68471])) {
            $phone = $order->user->phone;
            $fUser = ShopFuser::findOne(['user_id' => $order->user_id]);
            if (!empty($fUser->id)) {
                return SsTask::createNewTask(
                    SendOrderSmsTaskHandler::className(),
                    ['phone' => $phone, 'text' => $text, 'fuser_id' => $fUser->id]
                );
            }
        }

        return false;
    }

    public static function sendEmailCreateOrder($order, $subject, $template)
    {
        \Yii::info("Add task for sand email" . 'checkoutLog');
        return SsTask::createNewTask(
            SendMailGunEmailTaskHandler::className(),
            [
                'email' => $order->user->email,
                'composeClass' => $order::className(),
                'composeEntity' => 'order',
                'composeEntityId' => $order->id,
                'template' => $template,
                'subject' => $subject
            ]
        );
    }

//    public static function getCheckoutCompleteData(ShopOrder $order)
    public static function getCheckoutCompleteData($order)
    {
        //Данные по региону доставки в данном месте не актуальны, так как адрес передается в соответствующем блоке при инициализации и далее
        //Дублировать его тут не требуется
        //$deliveryData = self::getDeliveryData();
        $clientData = self::getClientData();

        return [
            "orderId" => $order->order_number, // Id заказа KFSS
            "orderGuid" => $order->guid->getGuid(), // GUID заказа сайта (необязательный параметр)
            "takeToWork" => (bool)$order->do_not_need_confirm_call, // Признак того, что заказ нужно взять сразу в обеспечение без обзвона контакт центром
            "phone" => \common\helpers\Strings::onlyInt(\Yii::$app->shop->shopFuser->phone ?: $order->user->phone), // Номер телефона
//            "regionName" => $deliveryData['regionName'], // Наименование региона
//            "regionCode" => $deliveryData['regionCode'], // КЛАДР код региона
            //В связи с переходом на прямую передачу канала продаж указывать данный флаг не требуется ибо может встать не тот канал продаж
//            "isLanding" => $order->getIsSourceCpa(), // Признак того, что заказ оформлен на лэндинге
            "isLanding" => false,
            'surname' => $clientData['surname'] ?? null,
            'name' => $clientData['name'] ?? null,
            'patronymic' => $clientData['patronymic'] ?? null,
        ];
    }

    public static function getPosition($baskets)
    {
        $positions = [];


        if ($baskets) {
            foreach ($baskets as $shopBasket) {
                /** @var $shopBasket ShopBasket */

                //Подарок в списке товаров отправлять не надо
                //СЕРВИСНЫЙ СБОР!?
                if ($shopBasket->price < 2) {
                    continue;
                }

                $product = \common\models\Product::findOne($shopBasket->product_id);

                if (empty($product->kfss_id) || !$product) {
                    continue;
                }

                $position = [
                    'offcntId' => $product->kfss_id,
                    'quantity' => (int)$shopBasket->quantity,
//                    'price' => (int)$shopProduct->basePrice(),
//                    'price' => (int)$product->new_price,
                    //В корзину товар может попасть не по текущей цене, а например по цене канала продаж
                    //Так что берем цену именно из корзины
                    'price' => (int)$shopBasket->price,
                ];

                if (!empty($shopBasket->kfss_position_id)) {
                    $position['positionId'] = $shopBasket->kfss_position_id;
                }

                $positions[] = $position;
            };
        }

        return $positions;
    }

    /**
     *  Данные по клиенту для обращения в АПИ КФСС
     * @param array $defaults
     * @return array
     */
    public static function getClientData($defaults = [])
    {
        //Данные пользователя - это то что ввели в форму в корзине, это данные из fuser->additional
        //Пользователь это:
        //Авторизованный
        //Если не авторизованный, то найденный по телефону

        $fuser = \Yii::$app->shop->shopFuser;
        $fuserPhone = $fuser->phone;

        $user = User::getUser();
        if (!$user && $fuserPhone){
//            \Yii::error("Ищу пользователя по телефону", 'kfss');

            $userClassName = \Yii::$app->user->identityClass;
            $user = $userClassName::findByPhone($fuserPhone);
        }

        if (!$user){
            $user = new QuickOrder();
        }

        if ($userData = Api::getUserData()){

            //Если пользователь авторизован или ненаходится совсем - то загрузка данных в модель пройдет нормально, данные из формы и модель совпадают
            //А если пользователь гость, то форма QuickOrder, но если при этом пользователь нашелся по телефону - то модель будет другая
            //Приходится обхоть разность моделей что бы подгрузиь данные

            //При работе через АПИ сайта уже не актуально
            if (false) {
                if ($user instanceof QuickOrder || User::isAuthorize()) {
                    $user->load($userData);
                } else {
                    $user->load([StringHelper::basename($user::className()) => $userData['QuickOrder']]);
                }
            }


            $userAttributes = [
                'phone' => trim($userData['phoneNumber']) ?? '',
                'email' => $userData['email'] ?? '',
                'name' => $userData['firstName'] ?? '',
                'patronymic' => $userData['middleName'] ?? '',
                'surname' => $userData['lastName'] ?? '',
            ];
            $user->setAttributes($userAttributes);

        }

        if ($user){
            $userGuid = $user->guid ? $user->guid->getGuid() : null;
            $userEmail = $user->email;
//            $userPhone = Strings::onlyInt(\Yii::$app->shop->shopFuser->phone ?: $user->phone);
            $userPhone = $user->phone;

            $name = $user->name ?: $defaults['name'] ?? '';
            $surname = $user->surname ?: $defaults['surname'] ?? '';
            $patronymic = $user->patronymic ?: $defaults['patronymic'] ?? '';
        }else{
            $userGuid = null;
            $userEmail = null;
            $userPhone = null;
            $name = '';
            $surname = null;
            $patronymic = null;
        }

        return [
            //'id' => null,
            'guid' => $userGuid,
            'email' => trim($userEmail),
            'phone' => $userPhone,
            'surname' => trim($surname),
            'name' => trim($name),
            'patronymic' => trim($patronymic),
            'birthDate' => null,
            'gender' => false,
            'address' => null,
        ];
    }

    //$order - или заказ или фузер
    public static function getCreateData($defaults = [])
    {
        //$baskets = \Yii::$app->shop->shopFuser->getShopBaskets()->orderBy('id ASC')->all();

        $result = [
            //'guid' => null, //гуид заказа
            'client' => self::getClientData($defaults['client'] ?? []),
//            'positions' => self::getPosition($baskets),
        ];

        if (!empty($defaults['sale_channel_id'])){
            $result['saleChannelId'] = $defaults['sale_channel_id'];
        }

        return $result;
    }

    public static function getDeliveryData($dadata = false)
    {
        if ($dadata){

//            \Yii::error($dadata, 'debug');

            $regionName = $dadata['data']['region_with_type'];
            $regionKladr = $dadata['data']['region_kladr_id'];

            $cityName = $dadata['data']['city_with_type'] ?: $dadata['data']['settlement'];
            $cityFias = $dadata['data']['city_kladr_id'];

            $streetName = $dadata['data']['street_with_type'] ?: '';
            $streetFias = $dadata['data']['street_kladr_id'] ?: '';

            $buildNumber = self::getDeliveryAddressHouseFull($dadata['data']) ?: '';
            $buildFias = $dadata['data']['house_kladr_id'] ?: '';

            $flat = $dadata['data']['flat'] ?: '';

            $delivaryPostalCode = $dadata['data']['postal_code'];
        } elseif (true) {
            $profile = \Yii::$app->shop->shopFuser->getProfileParams();

            $address = \Yii::$app->dadataSuggest->address;

            $regionName = @$profile['Region'] ?: @$address->data['region_with_type'] ?: 'Москва';
            $regionKladr = @$profile['region_kladr_id'] ?: @$address->data['region_kladr_id'] ?: '7700000000000';

            $cityName = @$profile['city'] ?: @$address->data['city_with_type'] ?: @$address->data['settlement'] ?: 'Москва';
            $cityFias = @$profile['city_kladr_id'] ?: @$address->data['city_kladr_id'] ?: '7700000000000';

            $streetName = @$profile['StreetName'] ?: @$address->data['street_with_type'] ?: '';
            $streetFias = @$profile['FiasCodeStreet'] ?: @$address->data['street_kladr_id'] ?: '';

            $buildNumber = @$profile['StreetNumber'] ?: self::getDeliveryAddressHouseFull($address->data) ?: '';
            $buildFias = @$profile['FiasCodeBuilding'] ?: @$address->data['house_kladr_id'] ?: '';

            $flat = @$profile['DoorNumber'] ?: @$address->data['flat'] ?: '';

            $delivaryPostalCode = @$profile['postal_code'] ?: @$address->data['postal_code'] ?: '101000';
        } else {
            $regionName = 'Москва';
            $regionKladr = '7700000000000';

            $cityName = null;
            $cityFias = null;

            $streetName = null;
            $streetFias = null;

            $buildNumber = null;
            $buildFias = null;

            $flat = null;

            $delivaryPostalCode = '101000';
        }

        return [
            'regionName' => $regionName,
            'regionCode' => $regionKladr,
            'districtName' => null,
            'disctrictCode' => null,
            'cityName' => $cityName,
            'cityCode' => $cityFias,
            'streetName' => $streetName,
            'streetCode' => $streetFias,
            'buildName' => $buildNumber,
            'buildCode' => $buildFias,
            'flat' => $flat,
            'zipCode' => $delivaryPostalCode,
        ];
    }

    public static function getDeliveryAddressHouseFull($geoobjectData)
    {
        $houseAndBlockWithTypeParts = [];
        if (!empty($geoobjectData['house_type'])) {
            $houseAndBlockWithTypeParts[] = $geoobjectData['house_type'];
        }
        if (!empty($geoobjectData['house'])) {
            $houseAndBlockWithTypeParts[] = $geoobjectData['house'];
        }
        if (!empty($geoobjectData['block_type'])) {
            $houseAndBlockWithTypeParts[] = $geoobjectData['block_type'];
        }
        if (!empty($geoobjectData['block'])) {
            $houseAndBlockWithTypeParts[] = $geoobjectData['block'];
        }

        return implode(' ', $houseAndBlockWithTypeParts);
    }

    public static function getDeliveryTypeData()
    {
        switch (\Yii::$app->shop->shopFuser->delivery_id) {
            case 5: //Курьерка
            case 9: //ПВЗ
                $deliveryTypeId = 1;
                break;
            default: //Почта
                $deliveryTypeId = 0;
                break;
        }

        $carrierId = 210; //Почта России

        if (true) {
            $pvz = \Yii::$app->shop->shopFuser->pvz;

            $pickpointId = null;

            //ПВЗ указываем только если у нас именно способ доставки ПВЗ (а не только если он был выбран, но потом сменен например)
            if (\Yii::$app->shop->shopFuser->delivery_id == 9 && $pvz && $pvz['id']) {
                $pickpointId = $pvz['id'];
            }

            //Курьерка
            if (\Yii::$app->shop->shopFuser->delivery_id == 5) {
                $carrierId = 310; //Автовыбор курьерки
            } elseif (\Yii::$app->shop->shopFuser->delivery_id == 9) { //ПВЗ
                $carrierId = !empty($pvz['carrierId']) ? $pvz['carrierId']: 610;
            }

        } else {
            $pickpointId = null;
        }

        return [
            'deliveryTypeId' => $deliveryTypeId,
            'carrierId' => $carrierId, // Id курьерской службы (не обязательно)
            'pickpointId' => $pickpointId,  // (Id пункта выдачи заказов (ПВЗ))
        ];
    }

    public static function getPaymentTypeData()
    {
        switch (\Yii::$app->shop->shopFuser->pay_system_id) {
            case \Yii::$app->kfssApiV2::PAY_SYSTEM_ID_CARD:
                $kfssPaymentTypeId = \Yii::$app->kfssApiV2::KFSS_PAY_TYPE_ID_CARD;
                break;
            case \Yii::$app->kfssApiV2::PAY_SYSTEM_ID_NAL:
            default:
                $kfssPaymentTypeId = \Yii::$app->kfssApiV2::KFSS_PAY_TYPE_ID_NAL;
                break;
        }
        return $kfssPaymentTypeId;
    }

    public static function getCouponData($coupon = '')
    {
        return [
            'orderId' => \Yii::$app->shop->shopFuser->external_order_id,
            'code' => $coupon
        ];
    }

}