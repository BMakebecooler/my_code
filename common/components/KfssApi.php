<?php

namespace common\components;

use common\helpers\Msg;
use common\models\cmsContent\CmsContentElement;
use modules\shopandshow\models\shop\ShopBasket;
use modules\shopandshow\models\shop\ShopDiscount;
use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopProduct;
use skeeks\cms\base\Component;
use yii\di\Instance;
use yii\httpclient\Client;

/**
 * Класс работы с KFSS API
 * Class KfssApi
 * @package common\components
 */
class KfssApi extends Component
{

    const TIMEOUT = 20;
    /**
     * @var Client $httpClient
     */
    public $httpClient;

    public $baseUrl;
    public $username;
    public $password;

    protected $debugMode = false;

    public static $order;

    public $isDisable = true;

    private $_externalOrderId;

    public function init()
    {
        if (is_array($this->httpClient)) {
            $this->httpClient = Instance::ensure($this->httpClient);

            $this->httpClient->baseUrl = $this->baseUrl;
        }
        $this->_externalOrderId = \Yii::$app->shop->shopFuser->external_order_id;


        $phoneTester = [
            78000010003,
            79853333333,
            79775114700
        ];
        $currentPhone = '';

        if (!\Yii::$app->user->isGuest) {
            $currentPhone = \Yii::$app->user->identity->formatPhone;
        }


        if (in_array($currentPhone, $phoneTester)) {
            $this->isDisable = false;
        }

        // hardcore if test use phone
        $this->isDisable = true;


        parent::init();

    }

    public function createOrder()
    {
        if ($this->isDisable) {
            return false;
        }
        $this->info("Creating order for fUser #" . \Yii::$app->shop->shopFuser->id);
        $requestData = $this->getOrderCreateData();
        $this->info("RequestData: " . print_r($requestData, true));

        $request = $this->httpClient->createRequest()
            ->setMethod('POST')
            ->setFormat(Client::FORMAT_JSON)
            ->setUrl('order')
            ->setData($requestData)
            ->setOptions([
                'timeout' => self::TIMEOUT,
            ])
            ->addHeaders(['Authorization' => 'Basic ' . base64_encode("{$this->username}:{$this->password}")]);

        \Yii::info('POST order, data ' . print_r($requestData, true), 'kfssapi_send');
        $response = $request->send();

        $responseData = $response->getData();

        if ($response->isOk) {
            $this->info("Order created. ResponseData: " . print_r($responseData, true));
        } else {
            $this->error("CreateOrder. Bad response. Status code: " . $response->getStatusCode() . " Headers: " . print_r($response->headers, true));
            $responseData = $response->getData();
        }

        \Yii::$app->cache->set($this->_cacheKey(), false, 60);
        return $this->prepareResponseData($response->getData());
    }

    public function updateOrder()
    {
        if ($this->isDisable) {
            return false;
        }
        $orderIdKfss = $this->_externalOrderId;

        //Возможн это попытка обновления данных заказа до его инициализации в КФСС
        //например при добавлении товара в корзину до первого захода в нее (что является триггером создания заказа в КФСС)
        //Так что отсутствие заказа вполне штатная ситуация в данном случае

        if ($orderIdKfss) {
            $requestUrl = sprintf("%s/%s", 'order', $orderIdKfss);

            $this->info("Updating order #$orderIdKfss.");
            $requestData = $this->getOrderUpdateData();
            $this->info("RequestData: " . print_r($requestData, true));

            $request = $this->httpClient->createRequest()
                ->setMethod('PUT')
                ->setFormat(Client::FORMAT_JSON)
                ->setUrl($requestUrl)
                ->setData($requestData)
                ->setOptions([
                    'timeout' => self::TIMEOUT,
                ])
                ->addHeaders(['Authorization' => 'Basic ' . base64_encode("{$this->username}:{$this->password}")]);

            \Yii::info('PUT ' . $requestUrl . ', data ' . print_r($requestData, true), 'kfssapi_send');
            $response = $request->send();
            $responseData = $response->getData();

            //Проверяем что в ответ пришел заказ
            if (empty($responseData['orderId'])) {
                $responseData = $this->recreateOrder(true);
            }

            if ($response->isOk) {
                $this->info("Updating order #$orderIdKfss. ResponseData: " . print_r($responseData, true));
//                var_dump('Response OK');
            } else {
                $this->error("UpdateOrder #$orderIdKfss. Bad response. Status code: " . $response->getStatusCode());
            }

            \Yii::$app->cache->set($this->_cacheKey(), false);
            return $this->prepareResponseData($responseData);
        } else {
            //error
            $this->error("Updating order error. Wrong order id = '$orderIdKfss'.");
            return false;
        }
    }

    /**
     * @param null $orderIdKfss - это идентификатор заказа KFSS, (обязательный параметр!)
     * @param int $reasonId - Id причины отмены заказа, (необязательный параметр, по умолчанию -319)
     * @param string $reasonNote - Комментарий отмены заказа, (необязательный параметр, по умолчанию 'Отмена с сайта через АПИ')
     * @return bool
     */
    public function cancelOrder($orderIdKfss = null, $reasonId = -319, $reasonNote = '')
    {
        if ($this->isDisable) {
            return false;
        }
        /*

        Возможные варианты значений reasonId:
           4 - Нет позиций
          12 - Условия акции не выполнены
         210 - По инициативе Компании
          28 - Тестовый заказ
        -302 - заказ с нулевой стоимостью
        -310 - Некорректный номер телефона
        -311 - Объединен с другим заказом/дубль
        -314 - Блокировка клиента
        -315 - Заказ создан по ошибке
        -318 - Длительное ожидание подтверждения на сайте
        -319 - Другое (указать в комментарии)

        */

        $orderIdKfss = $orderIdKfss ?: $this->_externalOrderId;

        if ($orderIdKfss) {
            $requestUrl = sprintf("%s/%s", 'order', $orderIdKfss);

            $requestData = [
                'reasonId' => -319,
                'reasonNote' => '',
            ];
            $this->info("Cancel order #$orderIdKfss.");
            $this->info("[#$orderIdKfss] RequestData: " . print_r($requestData, true));

            $request = $this->httpClient->createRequest()
                ->setMethod('DELETE')
                ->setFormat(Client::FORMAT_JSON)
                ->setUrl($requestUrl)
                ->setOptions([
                    'timeout' => self::TIMEOUT,
                ])
                ->setData($requestData)
                ->addHeaders(['Authorization' => 'Basic ' . base64_encode("{$this->username}:{$this->password}")]);

            \Yii::info('DELETE ' . $requestUrl . ', data ' . print_r($requestData, true), 'kfssapi_send');
            $response = $request->send();
            $responseData = $response->getData();

            if ($response->isOk) {
                $this->info("Cancel order #$orderIdKfss. ResponseData: " . print_r($responseData, true));
//                var_dump('Response OK');
            } else {
                $this->error("CancelOrder #$orderIdKfss. Bad response. Status code: " . $response->getStatusCode());
            }

            \Yii::$app->cache->set($this->_cacheKey(), false);
            return $this->prepareResponseData($responseData);
        } else {
            //error
            $this->error("Cancel order error. Wrong order id = '$orderIdKfss'.");
            return false;
        }

    }

    public function getOrder($source = null)
    {
        if ($this->isDisable) {
            return false;
        }
        $orderId = $this->_externalOrderId;

        if (!$orderId) {
            return false;
        }

//        if (!empty(self::$order)) {
//            $this->info("GetOrder #{$orderId} [local" . ($source ? ' / ' . $source : '') . "]");
//            return self::$order;
//        }


        $data = \Yii::$app->cache->get($this->_cacheKey());
        if (!($data === false)) {
            $this->info("GetOrder #{$orderId} from cache [local" . ($source ? ' / ' . $source : '') . "]");
            return $data;
        }

        $this->info("GetOrder #{$orderId} [API" . ($source ? ' / ' . $source : '') . "]");

        $requestUrl = sprintf("%s/%s", 'order', $orderId);
        $userAuthLogPas = "{$this->username}:{$this->password}";

        $request = $this->httpClient->createRequest()
            ->setMethod('GET')
//            ->setFormat(Client::FORMAT_JSON)
            ->setOptions([
                'timeout' => self::TIMEOUT,
            ])
            ->setUrl($requestUrl)
            ->addHeaders(['Authorization' => 'Basic ' . base64_encode($userAuthLogPas)]);

        \Yii::info('GET ' . $requestUrl, 'kfssapi_send');
        $response = $request->send();
        $responseData = $response->getData();


        $needRecreateOrder = false;

        //Проверяем что в ответ пришел заказ
        if (empty($responseData['orderId'])) {
            $needRecreateOrder = true;
        } else {
            //Проверяем что дата заказа равна текущей (условие корректного обсчета в кфсс)
            $orderDateTs = !empty($responseData['createDate']) ? strtotime($responseData['createDate']) : time();
            if (date('Y-m-d') != date('Y-m-d', $orderDateTs)) {
                $needRecreateOrder = true;
            }
        }

        if ($needRecreateOrder) {
            $responseData = $this->recreateOrder(true);
        }

        if ($response->isOk) {
//            var_dump('Response OK');
        } else {
            $this->error("GetOrder #{$orderId}. Bad response. Status code: " . $response->getStatusCode()) . " Headers: " . print_r($response->headers, true);
            //var_dump($response);
        }


        $responseFormat = $this->prepareResponseData($responseData);
        \Yii::$app->cache->set($this->_cacheKey(), $responseFormat);
        return $responseFormat;
    }

    /**
     * @param $order ShopOrder
     * @return array|bool|mixed
     */
    public function checkoutOrder($order)
    {
        if ($this->isDisable) {
            return false;
        }

        $orderIdKfss = $order->order_number;

        if ($orderIdKfss) {
            $requestUrl = sprintf("%s", 'ordersend');

            $requestData = $this->getOrderSendData($order);
            $this->info("checkoutOrder, #" . $orderIdKfss . " RequestData: " . print_r($requestData, true));

            $request = $this->httpClient->createRequest()
                ->setMethod('POST')
                ->setFormat(Client::FORMAT_JSON)
                ->setUrl($requestUrl)
                ->setOptions([
                    'timeout' => self::TIMEOUT,
                ])
                ->setData($requestData)
                ->addHeaders(['Authorization' => 'Basic ' . base64_encode("{$this->username}:{$this->password}")]);

            \Yii::info('POST '.$requestUrl.', data ' . print_r($requestData, true), 'kfssapi_send');
            $response = $request->send();
            $responseData = $response->getData();

            if ($response->isOk) {
                $this->error("Checkout order #$orderIdKfss. ResponseData: " . print_r($responseData, true) . ". RequestData :" . print_r($requestData, true));
//                var_dump('Response OK');
            } else {
                $this->error("API::CheckoutOrder #$orderIdKfss. Bad response. Status code: " . $response->getStatusCode() . " Headers: " . print_r($response->headers, true));
            }

            \Yii::$app->cache->set($this->_cacheKey(), false);
            return $response;
        } else {
            //error
            return false;
        }

    }

    public function addCoupon($coupon = '')
    {
        if ($this->isDisable) {
            return false;
        }
        if (!$this->_externalOrderId) {
            return false;
        }

        $requestData = $this->getAddCouponData($coupon);
        $this->info("AddCoupon, #" . $this->_externalOrderId . ", RequestData: " . print_r($requestData, true));

        $request = $this->httpClient->createRequest()
            ->setMethod('POST')
            ->setFormat(Client::FORMAT_JSON)
            ->setUrl('promo')
            ->setOptions([
                'timeout' => self::TIMEOUT,
            ])
            ->setData($requestData)
            ->addHeaders(['Authorization' => 'Basic ' . base64_encode("{$this->username}:{$this->password}")]);


        \Yii::info('POST promo, data ' . print_r($requestData, true), 'kfssapi_send');
        $response = $request->send();

        $responseData = $response->getData();

        if ($response->isOk) {
            $this->error("AddCoupon, ResponseData: " . print_r($responseData, true));
//            var_dump('Response OK');
        } else {
            $this->error("AddCoupon. Bad response. Status code: " . $response->getStatusCode() . " Headers: " . print_r($response->headers, true));
            $responseData = $response->getData();
        }

        \Yii::$app->cache->set($this->_cacheKey(), false);

        return $response->getData();
    }

    public function getOrderCreateData()
    {
        return [
            //'guid' => null,
            'client' => $this->getClientData(),
            'positions' => $this->getOrderPositions(),
            'delivery' => $this->getOrderDeliveryData(),
        ];
    }

    public function getOrderUpdateData()
    {
        return [
            'positions' => $this->getOrderPositions(),
            'delivery' => $this->getOrderDeliveryData(), //Доставка будет позже
        ];
    }

    public function getOrderSendData(ShopOrder $order)
    {
        $deliveryData = $this->getOrderDeliveryData();

        return [
            'orderId' => $order->order_number,
            'orderGuid' => $order->guid->getGuid(),
            'takeToWork' => $order->do_not_need_confirm_call, //Заказ без участия КЦ
            'phone' => $order->user->phone,
            'regionName' => $deliveryData['deliveryAddress']['regionName'],
            'regionCode' => $deliveryData['deliveryAddress']['regionCode'],
        ];
    }

    public function getOrderPositions()
    {

        $orderPositions = [];

        $baskets = \Yii::$app->shop->shopFuser->getShopBaskets()->orderBy('id ASC')->all();

        if ($baskets) {
            foreach ($baskets as $shopBasket) {
                /** @var $shopBasket ShopBasket */

                //Подарок в списке товаров отправлять не надо
                if ($shopBasket->price < 2) {
                    continue;
                }

                $cmsContentElement = \common\lists\Contents::getContentElementById($shopBasket->product->id);
//                $mainProduct = \common\lists\Contents::getContentElementById($shopBasket->main_product_id);
                $shopProduct = ShopProduct::getInstanceByContentElement($cmsContentElement);

                if (empty($shopBasket->cmsContentElement->kfss_id)) {
                    $this->error("Товар {$shopBasket->name} [sBasketID={$shopBasket->id}, productId={$shopBasket->product_id}] отсутствует KFSS_ID");
                    continue;
                }

                $position = [
                    'offcntId' => $shopBasket->cmsContentElement->kfss_id,
                    'quantity' => (int)$shopBasket->quantity,
                    'price' => (int)$shopProduct->basePrice(),
                ];

                if (!empty($shopBasket->kfss_position_id)) {
                    $position['positionId'] = $shopBasket->kfss_position_id;
                }

                $orderPositions[] = $position;
            };
        }

        return $orderPositions;
    }

    public function getClientData()
    {

        if (\Yii::$app->user->isGuest) {
            $userGuid = null;
            $userEmail = null;
            $userPhone = null;
            $name = null;
            $surname = null;
        } else {
            $user = \common\helpers\User::getUser();
            $userGuid = $user->guid->getGuid();
            $userEmail = $user->email;
            $userPhone = $user->phone;
            $name = $user->name;
            $surname = $user->surname;
        }

        return [
            //'id' => null,
            'guid' => $userGuid,
            'email' => $userEmail,
            'phone' => $userPhone,
            'surname' => $surname,
            'name' => $name,
            'patronymic' => null,
            'birthDate' => null,
            'gender' => false,
            'address' => null,
        ];
    }

    public function getOrderDeliveryData()
    {
        switch (\Yii::$app->shop->shopFuser->delivery_id){
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
            $profile = \Yii::$app->shop->shopFuser->getProfileParams();

            $address = \Yii::$app->dadataSuggest->address;

            $regionName = @$profile['region'] ?: @$address->data['region_with_type'] ?: 'Москва';
            $regionKladr = @$profile['region_kladr_id'] ?: @$address->data['region_kladr_id'] ?: '7700000000000';

            $cityName = @$profile['city'] ?: @$address->data['city_with_type'] ?: @$address->data['settlement'] ?: 'Москва';
            $cityFias = @$profile['city_kladr_id'] ?: @$address->data['city_kladr_id'] ?: '7700000000000';

            $streetName = @$profile['StreetName'] ?: @$address->data['street_with_type'] ?: '';
            $streetFias = @$profile['FiasCodeStreet'] ?: @$address->data['street_kladr_id'] ?: '';

            $buildNumber = @$profile['BuildNumber'] ?: @$address->data['house'] ?: '';
            $buildFias = @$profile['FiasCodeBuilding'] ?: @$address->data['house_kladr_id'] ?: '';

            $flat = @$profile['DoorNumber'] ?: @$address->data['DoorNumber'] ?: '';

            $delivaryPostalCode = @$profile['postal_code'] ?: @$address->data['postal_code'] ?: '101000';

            $pvz = \Yii::$app->shop->shopFuser->pvz;

            $pickpointId = null;

            //ПВЗ указываем только если у нас именно способ доставки ПВЗ (а не только если он был выбран, но потом сменен например)
            if (\Yii::$app->shop->shopFuser->delivery_id == 9 && $pvz && $pvz['id']){
                $pickpointId = $pvz['id'];
            }

            //Курьерка
            if (\Yii::$app->shop->shopFuser->delivery_id == 5){
                $carrierId = 310; //Автовыбор курьерки
            }elseif (\Yii::$app->shop->shopFuser->delivery_id == 9){ //ПВЗ
                $carrierId = 610; //Boxberry
            }

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

            $pickpointId = null;
        }

        return [
            'deliveryTypeId' => $deliveryTypeId,
            'carrierId' => $carrierId, // Id курьерской службы (не обязательно)
            'pickpointId' => $pickpointId,  // (Id пункта выдачи заказов (ПВЗ))
            'deliveryAddress' =>
                [
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
                ],
        ];
    }

    private function prepareResponseData($responseData)
    {

        //Для упрощения нахождения позиции проиндексируем по ID
        if (!empty($responseData['positions'])) {
            $responseData['positions'] = \common\helpers\ArrayHelper::index($responseData['positions'], 'offcntId');
        }

        self::$order = $responseData;

        return self::$order;
    }

    public function getAddCouponData($coupon = '')
    {
        return [
            'orderId' => $this->_externalOrderId,
            'code' => $coupon
        ];
    }

    //Тут временно

    /**
     * Пересчет заказа текущего фузера на основе данных заказа из АПИ КФСС
     *
     * @param null $orderKfss
     * @return bool
     */
    public function recalculateOrder($orderKfss = null)
    {

        $kfssOrderId = $this->_externalOrderId;
        $shopBaskets = \Yii::$app->shop->shopFuser->getShopBaskets()->orderBy('id ASC')->all();

        $this->info("RecalcOrder #{$kfssOrderId}");

        if ($shopBaskets) {
            //$shopBaskets = \common\helpers\ArrayHelper::index($shopBaskets, 'id');

            if (!$orderKfss && !$kfssOrderId) {
                //error
                $this->error("RecalcOrder #{$kfssOrderId}. Ошибка! Нет заказа/номера заказа");
                return true;
            } else {
                if (!$orderKfss) {
                    $orderKfss = $this->getOrder(__METHOD__);
                }

                $this->recalculateBaskets($shopBaskets, $orderKfss);
                $this->recalculateFuser();

                return true;
            }
        }

        return true;
    }

    public  function orderPay($orderKfss = null, $orderPayId, $bankId = 40) {
        if(!empty($orderKfss)) {
            if(!empty($orderPayId)){
                $requestData = [
                    "OrderId"    => $orderKfss,
                    "ExternalId" => $orderPayId,
                    "BankId"     => $bankId
                ];

                $request = $this->httpClient->createRequest()
                    ->setMethod('POST')
                    ->setFormat(Client::FORMAT_JSON)
                    ->setUrl('orderpay')
                    ->setOptions([
                        'timeout' => self::TIMEOUT,
                    ])
                    ->setData($requestData)
                    ->addHeaders(['Authorization' => 'Basic ' . base64_encode("{$this->username}:{$this->password}")]);

                $request->send();
            } else {
                \Yii::error('Empty order pay id. Cant sent payment id.');
            }
        } else {
            \Yii::error('Empty order id kfss. Cant sent payment id.');
        }
    }

    public function recalculateBaskets($shopBaskets, $orderKfss = null)
    {
        $this->info("RecalcBaskets");

        if (!$orderKfss) {
            $orderKfss = $this->getOrder(__METHOD__);
        }

        $shopBasketsKfssId = [];

        /** @var ShopBasket $shopBasket */
        foreach ($shopBaskets as $shopBasket) {
            $this->recalculateBasket($shopBasket, $orderKfss);

            //Соберем все kfss_id товаров корзины для сравнения со списком товаров из КФСС. Возможно КФСС пришлет доп.товары (подарки)
            if ($shopBasket->cmsContentElement->kfss_id) {
                $shopBasketsKfssId[$shopBasket->id] = $shopBasket->cmsContentElement->kfss_id;
            }
        }

        if ($orderKfss) {

            //Проверка на дополнительные товары (подарки)
            if (!empty($orderKfss['positions'])) {
                $kfssAdditionalProducts = array_diff(array_keys($orderKfss['positions']), $shopBasketsKfssId);

                if ($kfssAdditionalProducts) {
                    foreach ($kfssAdditionalProducts as $additionalProductKfssId) {
                        $this->addProduct($additionalProductKfssId);
                    }
                }
            }

            //Проверка на то что какого то товара уже нет в заказе (подарка или какого либо другого)
            $kfssPositionsIds = !empty($orderKfss['positions']) ? array_keys($orderKfss['positions']) : [];
            if ($kfssRemovedProducts = array_diff($shopBasketsKfssId, $kfssPositionsIds)) {
                foreach (array_keys($kfssRemovedProducts) as $removedShopBasketId) {
                    \Yii::info("Remove product $removedShopBasketId", 'kfss');
                    $this->removeProduct($removedShopBasketId);
                }
            }
        }

        return true;
    }

    public function recalculateBasket(ShopBasket $shopBasket, $orderKfss = null)
    {
        if (!$orderKfss) {
            $orderKfss = $this->getOrder(__METHOD__);
        }

        //Возможно это добавление в корзину до инициализации обмена по КФСС АПИ
        if ($orderKfss) {
            $product = $shopBasket->product;

            $cmsContentElement = \common\lists\Contents::getContentElementById($product->id);
            //$shopProduct = ShopProduct::getInstanceByContentElement($cmsContentElement); //$parentElement Записываем либо цену предложения либо главного товара
            $parentElement = $cmsContentElement->product;

//                    $kfssOffercntId = $shopBasket->cmsContentElement->kfss_id;
            $kfssOffcntId = $cmsContentElement['kfss_id'];

            $this->info("RecalcBasket #{$shopBasket->id} [KFSS_ID = {$kfssOffcntId}]");

            if ($kfssOffcntId && !empty($orderKfss['positions']) && !empty($orderKfss['positions'][$kfssOffcntId])) {
                $kfssElement = $orderKfss['positions'][$kfssOffcntId];
                //Скидки для товара
                $shopBasket->discount_price = $kfssElement['originalPrice'] - $kfssElement['price'];
                $shopBasket->discount_value = "";
                $shopBasket->price = $kfssElement['price'];
                $shopBasket->quantity = $kfssElement['quantity'];

                //Запишем все скидки связанные с данным товаром
                if (!empty($kfssElement['discounts'])) {
                    $discountNames = array_column($kfssElement['discounts'], 'name');
                    $shopBasket->discount_name = implode(' + ', $discountNames);
                }

                //Если нет идентификатора позиции в заказе или он новый - запишем
                if (!$shopBasket->kfss_position_id || $shopBasket->kfss_position_id != $kfssElement['positionId']) {
                    $shopBasket->kfss_position_id = (int)$kfssElement['positionId'];
                }
            } else {
                $this->error("RecalcBasket - No position for basket {$shopBasket->id}");
            }

            //$shopBasket->price = $shopProduct->basePrice();

            $shopBasket->name = $parentElement->getLotName();
            $shopBasket->main_product_id = $parentElement->id;

            $shopBasket->currency_code = 'RUB';

            if (!$shopBasket->save()) {
                $this->error("RecalcBasket.Save Ошибки: " . print_r($shopBasket->getErrors(), true));
                return false;
            }
        } else {
            $this->error("RecalcBasket - No KFSS order");
        }

        return true;
    }

    public function recalculateFuser()
    {
        $this->info("RecalcFuser #" . \Yii::$app->shop->shopFuser->id);

        \Yii::$app->shop->shopFuser->link('site', \Yii::$app->cms->site);

        ShopDiscount::fuserRecalculate(\Yii::$app->shop->shopFuser);
        \Yii::$app->shop->shopFuser->save();
    }

    /**
     * Добавление дополнительного товара в корзину (например подарок)
     *
     * @param $productKfssId
     * @param bool $asGift
     * @return bool
     */
    public function addProduct($productKfssId, $asGift = true)
    {
        $this->info("AddAdditionalProduct kfss_id=#{$productKfssId}");

        /** @var CmsContentElement $product */
        $product = CmsContentElement::find()->where(['kfss_id' => $productKfssId])->one();
        if ($product) {
            $orderKfss = $this->getOrder(__METHOD__);

            $kfssProduct = $orderKfss['positions'][$productKfssId];

            $productId = $product->id;
            $quantity = $kfssProduct['quantity'];

            $product = ShopProduct::find()->where(['id' => $productId])->one();

            if (!$product) {
                return false;
            }

            if ($product->measure_ratio > 1) {
                if ($quantity % $product->measure_ratio != 0) {
                    $quantity = $product->measure_ratio;
                }
            }

            /**
             * @var self $shopBasket
             */
            $shopBasket = ShopBasket::find()->where([
                'fuser_id' => \Yii::$app->shop->shopFuser->id,
                'product_id' => $productId,
                'order_id' => null,
            ])->one();

            if (!$shopBasket) {
                $shopBasket = new ShopBasket([
                    'fuser_id' => \Yii::$app->shop->shopFuser->id,
                    'product_id' => $product->id,
                    'quantity' => 0,
                ]);

                if ($asGift) {
                    $shopBasket->setIsGift(true);
                }
            }

            /**
             * Если товар был раннее удален то сбрасываем количество
             */
            if ($shopBasket->hasRemoved()) {
                $shopBasket->quantity = $quantity;
            } else {
                $shopBasket->quantity = $shopBasket->quantity + $quantity;
            }

            $shopBasket->has_removed = ShopBasket::HAS_REMOVED_FALSE; //При добавлении в корзину удаленного товара ставим его не удаленным

            $this->recalculateBasket($shopBasket);

        } else {
            $this->error("RecalcBaskets/AdditionalProductAdd.  Ошибка, не найден товар с kfss_id='{$productKfssId}'");
        }

        return true;
    }

    /**
     * Удаление товара из корзины так как его нет в списке товаров возвращенного KfssAPI (например подарка если удален основной товар)
     *
     * @param $shopBasketId
     * @return bool
     */
    public function removeProduct($shopBasketId)
    {
        $this->info("removeProduct shopBasketId=#{$shopBasketId}");

        /** @var CmsContentElement $product */
        $shopBasket = ShopBasket::findOne($shopBasketId);

        if ($shopBasket) {
            $shopBasket->has_removed = ShopBasket::HAS_REMOVED_TRUE;
            $shopBasket->save();
        } else {
            $this->error("RecalcBaskets/removeProduct.  Ошибка, не найден товар с shopBasketId='{$shopBasketId}'");
            return false;
        }

        return true;
    }

    /**
     * Пересоздание заказа если по имеющемуся номеру он ненаходится
     *
     * @param bool $needRecalculate
     * @return bool
     */
    private function recreateOrder($needRecalculate = false)
    {
        //Если был номер заказа то можем пробовать пересоздавать
        if ($kfssOrderId = $this->_externalOrderId) {
            $this->info("Recreate order #{$kfssOrderId}");
            //Перед созданием отменяем предыдущий заказ (хотя он уже может быть в отмене)
            $this->cancelOrder();
            $orderKfss = $this->createOrder();

            if ($orderKfss && !empty($orderKfss['orderId'])) {
                $kfssOrderId = $orderKfss['orderId'];
                \Yii::$app->shop->shopFuser->external_order_id = $kfssOrderId;
                \Yii::$app->shop->shopFuser->save();

                //Приводим корзину в соответствие с заказом из АПИ
                if ($needRecalculate) {
                    $this->recalculateOrder($orderKfss);
                }

                return $orderKfss;
            } else {
                //Не удалось создать заказ
                $this->error("Can't recreate kfss order #{$kfssOrderId} for fUser='" . \Yii::$app->shop->shopFuser->id . "'");
            }
        } else {
            $this->error("Empty KFSS order id for fUser='" . \Yii::$app->shop->shopFuser->id . "'");
        }
        return false;
    }

    public function info($msg)
    {

//        \Yii::info($msg, 'kfssapi');
        return true;
    }

    public function error($msg)
    {

//        \Yii::error($msg, 'kfssapi');
        return true;
    }


    private function _cacheKey()
    {
        return 'kfss_order_' . $this->_externalOrderId;
    }
}