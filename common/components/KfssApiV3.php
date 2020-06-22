<?php

namespace common\components;

use common\helpers\Api;
use common\helpers\Common;
use common\helpers\Order;
use common\models\cmsContent\CmsContentElement;
use common\models\Product;
use common\models\Setting;
use common\models\ShopBasket;
use common\models\ShopOrder;
//use modules\shopandshow\models\shop\ShopBasket;
use common\models\SsShopFuserDiscount;
use modules\shopandshow\models\shop\ShopDiscount;
use modules\shopandshow\models\shop\ShopProduct;

//use skeeks\cms\base\Component;
use yii\base\Component;
use yii\di\Instance;
use yii\httpclient\Client;

/**
 * Класс работы с KFSS API
 * Class KfssApi
 * @package common\components
 */

// [POST] cartbatch - инициализация заказа
// [GET] orderbatch/{id} - get order data //full?
// [PUT] cartbatch/{:orderId}
// [POST] promobatch - apply coupon and get order data

class KfssApiV3 extends Component
{

    const TIMEOUT = 2;
    const LOCKSTOCK_TIME_COOCKIE_NAME = 'remote-recalculate-date';
    const LOCKSTOCK_TIME = YII_ENV == 'prod' ? 60 * 30 : 60;

    const PAY_SYSTEM_ID_NAL = 1;
    const PAY_SYSTEM_ID_CARD = 2;

    const KFSS_PAY_TYPE_ID_NAL = 50;
    const KFSS_PAY_TYPE_ID_CARD = 240;

    const SERVICE_CHARGE = 50;
    /**
     * @var Client $httpClient
     */
    public $httpClient;

    public $baseUrl;
    public $username;
    public $password;

    public $kfssStatuses = [
        'creating' => 1, //Создается
        'reserve' => -4, //Блокировка, Резерв
    ];

    protected $debugMode = false;

    public static $order;

    public $isDisable = false;

    //Использовать ли только принудительный вариант работы (по явной, "кнопочной" инициализации)
    public $forcedUseOnly = true;

    private $_externalOrderId;

    public function init()
    {
        if (is_array($this->httpClient)) {
            $this->httpClient = Instance::ensure($this->httpClient);
            $this->httpClient->baseUrl = $this->baseUrl;
        }

        if (\common\helpers\App::isConsoleApplication()) {
//            $this->isDisable = false;

            parent::init();
            return;
        }

        $this->_externalOrderId = \Yii::$app->shop->shopFuser->external_order_id;

        $phoneTester = [
            78000010003,
            79853333333,
            79775114700,
//            79260201211,
        ];
        $currentPhone = '';

        if (!\Yii::$app->user->isGuest) {
            $currentPhone = \Yii::$app->user->identity->formatPhone;
        }

        parent::init();
    }

    public function setKfssOrderId ($kfssOrderId) {
        $this->_externalOrderId = $kfssOrderId;
        \Yii::$app->shop->shopFuser->external_order_id = $kfssOrderId;
        \Yii::$app->shop->shopFuser->save();

        return true;
    }

    public function getShopFuser () {
        return \Yii::$app->shop->shopFuser;
    }

    //Инициализация заказа, пересчет и получение инфорамацци по заказу.
    //Принимается: клиент и состав корзины
    //Регион!?
    //В ответе ДОСТАВКА НЕ СЧИТАЕТСЯ!
    //https://wiki.shopandshow.ru/pages/viewpage.action?pageId=15240485

    /**
     * @param array $defaults
     * @return array|mixed|null
     */
    public function initCartBatch($defaults = []) //POST
    {
        $clientData = Order::getClientData($defaults['client'] ?? []);
        $baskets = \Yii::$app->shop->shopFuser->getShopBaskets()->orderBy('id ASC')->all();

        $data = [
            'client' => [
                'guid' => $clientData['guid'],
                'phone' => $clientData['phone'],
            ],
            'positions' => Order::getPosition($baskets),
        ];

        if (!empty($defaults['sale_channel_id'])) {
            $data['saleChannelId'] = $defaults['sale_channel_id'];
        }

        if ($kfssOrderId = $this->getKfssOrderId()) {
            $this->cancel($kfssOrderId);
        }

        return $this->_call('POST', 'cartbatch', $data);
    }

    //Проверяет что текущий заказ есть и с ним все хорошо и можно работать (не требуется создание/пересоздание)
    public function canManageOrder($kfssOrderId = null)
    {
        $kfssOrderId = $this->getKfssOrderId($kfssOrderId);

        if ($kfssOrderId) {
            $isNormal = true;
            //Получаем именно минимально необходимые данные по заказу
            //TODO Когда допилят метод статуса - перейти на его использование
            $responseData = $kfssOrderId ? $this->_call('GET', 'order/' . $kfssOrderId) : false;

            //Проверяем что в ответ пришел заказ, если это не так - то надо пересоздать
            if (!$responseData || empty($responseData['orderId'])) {
                $isNormal = false;
            }

            //Заказ все таки есть - проверяем кейсы влияющие на необходимость пересоздания
            if ($responseData && !empty($responseData['orderId'])) {

                //Проверяем корректность даты (актуально для заказов в статусе Создается)
                if ($responseData['statusId'] == $this->kfssStatuses['creating'] || $responseData['statusId'] == $this->kfssStatuses['reserve']) {
                    //Проверяем что дата заказа равна текущей (условие корректного обсчета в кфсс)
                    $orderDateTs = !empty($responseData['createDate']) ? strtotime($responseData['createDate']) : time();
                    if (date('Y-m-d') != date('Y-m-d', $orderDateTs)) {
                        $isNormal = false;
                    }
                } else {
                    //Статус отличен от Создается - надо пересоздавать что бы была возможность с ним работать
                    $isNormal = false;
                }

            }

        }else{
            //Нет даже номера заказа - точно не нормально
            $isNormal = false;
        }

        return $isNormal;
    }

    //Получить расширенную информацию по заказу
    //Без пересчета?
    //https://wiki.shopandshow.ru/pages/viewpage.action?pageId=15240483
    public function getOrderBatch($kfssOrderId = null, $update = false) //GET
    {
        $kfssOrderId = self::getKfssOrderId($kfssOrderId);

        if ($kfssOrderId) {

            if ($update) {
                $responseRecalc = self::initRecalculateOrder($kfssOrderId);
            }

            //Учесть возможную необходимость пересоздания заказа (изменилась дада, изменился статус и тп)
            $responseData = $this->_call('GET', 'orderbatch/' . $kfssOrderId);

            if (!empty($responseData['orderId'])) {
                $kfssOrder = $responseData;
                //Для удобства сравнения товаров при пересчетах и тп
                if (!empty($kfssOrder['positions'])) {
                    $kfssOrder['positions'] = \common\helpers\ArrayHelper::index($kfssOrder['positions'], 'offcntId');
                }

                if (isset($kfssOrder['delivery']['price'])){
                    \Yii::$app->session->set($this->_deliveryCacheKey($kfssOrderId), $kfssOrder['delivery']['price']);
                }
            }
        }

        return $kfssOrder ?? false;
    }

    //По списку товаров актуализирует состав корзины и возвращает полный пересчитанный заказ
    //Принимает: список товаров
    //В ответе ДОСТАВКА НЕ СЧИТАЕТСЯ!
    //https://wiki.shopandshow.ru/pages/viewpage.action?pageId=15240486
    public function updateCartBatch($kfssOrderId = null) //PUT
    {
        $kfssOrderId = self::getKfssOrderId($kfssOrderId);

        if ($kfssOrderId) {
            $baskets = \Yii::$app->shop->shopFuser->getShopBaskets()->orderBy('id ASC')->all();

            $data = [
                'positions' => Order::getPosition($baskets),
            ];

            $responseData = $this->_call('PUT', 'cartbatch/' . $kfssOrderId, $data);
        } else {
            $responseData = $this->initCartBatch();
        }

        if (!empty($responseData['orderId'])) {
            //Если заказ пересоздался - актуализируем номер
            if ($kfssOrderId != $responseData['orderId']){
                $kfssOrderId = $responseData['orderId'];
                $this->setKfssOrderId($kfssOrderId);
            }

            $kfssOrder = $responseData;
            //Для удобства сравнения товаров при пересчетах и тп
            if (!empty($kfssOrder['positions'])) {
                $kfssOrder['positions'] = \common\helpers\ArrayHelper::index($kfssOrder['positions'], 'offcntId');
            }

            if (isset($kfssOrder['delivery']['price'])){
                \Yii::$app->session->set($this->_deliveryCacheKey($kfssOrderId), $kfssOrder['delivery']['price']);
            }
        }

        return $kfssOrder ?? false;
    }

    //$hasChange - массив измененных данных, которые надо обновить
    public function updateOrder($hasChanges = [])
    {
        $fUser = \Yii::$app->shop->shopFuser;
        $kfssOrderId = $this->getKfssOrderId();
        $orderKfss = null;

        //Прежде чем  что то обновлять выясним, а все ли нормально с заказом, можно ли с ним работать
        $canManageOrder = true;
        //Если в процессе вызывался инит, то данные по клиенту и позициям там уже актуальные, запрашивать еще раз обновление не требуется
        $triggeredInit = false;
        //Флаг о явной необходимости пересоздания заказа
        $needRecreate = false;

        //Купон. Если пришло событие на обновление данных купона, а самого купона нет, то это отмена купона с пересозданием заказа
        //ЗЫ Добавление купона идет отдельно, в частности в АПИ корзины
        $couponData = Api::getCartCouponData();
        $couponCode = $couponData['code'] ?? '';

        if(isset($hasChanges['coupon']) && !$couponCode){
            $needRecreate = true;
        }

        //Для тестов, если надо отменить текущий заказ
//        $needRecreate = true;

        //Проверяем, все ли нормально с заказом, что с ним можно работаь
        if (!$kfssOrderId || $needRecreate || !$this->canManageOrder($kfssOrderId)) {
            //С заказом что то не так - может его и не было (первое обращение) или он не подходит по критериям. Пробуем пересоздать.
            $initResponse = $this->initCartBatch();
            $triggeredInit = true;
            if (!empty($initResponse['orderId'])) {
                \Yii::error("RecreateOrder. Canceled #{$kfssOrderId} -> Created #{$initResponse['orderId']}", 'common\components\kfssapiv3');

                $kfssOrderId = $initResponse['orderId'];
                $orderKfss = $initResponse;
                //Для удобства сравнения товаров при пересчетах и тп
                if (!empty($kfssOrder['positions'])) {
                    $kfssOrder['positions'] = \common\helpers\ArrayHelper::index($kfssOrder['positions'], 'offcntId');
                }
                $this->setKfssOrderId($kfssOrderId);
            }else{
                //Заказа получить не удалось
                $canManageOrder = false;
            }
        }

        //Продолжаем только если выяснили что с заказом все ок и работать можем
        if ($kfssOrderId && $canManageOrder) {
            //Обновляем данные только если указано что они изменились или у нас произошло пересоздание и надо заполнить все данные

            if (isset($hasChanges['deliveryType']) || $triggeredInit) {
                $isDeliveryTypeSet = $this->setDeliveryType($kfssOrderId, Order::getDeliveryTypeData());
            }
            if (isset($hasChanges['deliveryAddress']) || $triggeredInit) {
                $isDeliverySet = $this->setDeliveryAddress($fUser->external_order_id, Order::getDeliveryData());
            }
            if (isset($hasChanges['paymentType']) || $triggeredInit) {
                $isPaymentTypeSet = $this->setPaymentType($fUser->external_order_id, Order::getPaymentTypeData());
            }
            if (isset($hasChanges['products']) && !$triggeredInit) {
                $orderKfss = $this->updateCartBatch();
            }

            //* Пересчет доставки *//

            //Пересчет доставки надо вызывать отдельно
            //Пока непонятно что именно может повлиять на стоимость доставки, так что будем вызывать при каждом обращении
            if (true) {
                \Yii::$app->kfssApiV3->initRecalculateDelivery($kfssOrderId);
            }

            //* /Пересчет доставки *//

            //* LOCKSTOCK *//

            $lockStockResponseData = $this->lockStock($kfssOrderId);

            //FOR TESTS
            //TODO REMOVE AFTER TESTS
//            $responseData['lockstock'] = 1;
            $lockstock = $lockStockResponseData['isSuccess'] ? (int)$lockStockResponseData['isSuccess'] : 0;

            //Если товар не зарезервирован то способ оплаты не должен быть оплатой по карте
            if (!$lockstock && $fUser->pay_system_id == self::PAY_SYSTEM_ID_CARD) {
                //TODO В кфсс видимо останется неправильный тип оплаты. так что возможно надо будет еще раз дернуть смену типа оплаты
                $fUser->setPayment(self::PAY_SYSTEM_ID_NAL, false);
            }

            //* /LOCKSTOCK *//

            //Вот тут очень тонкий момент в плане соблюдения баланса кол-во запросов/пересчетов и получении актуальных данных
            $orderKfss = $this->getOrderBatch($kfssOrderId, true);

            //Проверим и запомним стоимость доставки и статус оплаты картой
            if (!empty($orderKfss['orderId'])) {
                $orderKfss['lockstock'] = $lockstock;
            }
        }else{
            \Yii::error("Не могу");
        }

        return $orderKfss;
    }

    //Применить купон (промокод) с пересчетом и возвратом расширенной информации по заказу
    //https://wiki.shopandshow.ru/pages/viewpage.action?pageId=15240489
    public function promoBatch()
    {

    }


    public function getKfssOrderId($kfssOrderId = null)
    {
        return !empty($kfssOrderId) && $kfssOrderId > 0 ? $kfssOrderId : $this->_externalOrderId;
    }


    //--------------------------------------------------------------------------------------------------//

    /** Получить текущий статус заказа.
     *
     * @param $kfssOrderId
     * @return array|bool|mixed
     */
    public function getStatus($kfssOrderId = null)
    {
        $kfssOrderId = self::getKfssOrderId($kfssOrderId);
        return $kfssOrderId ? $this->_call('GET', 'orderstatus/' . $kfssOrderId) : false;
    }

    /** Отмена указанного заказа
     *
     * @param $kfssOrderId
     * @return array|bool|mixed
     */
    public function _cancel($kfssOrderId)
    {
        return $this->_call('DELETE', 'order/' . $kfssOrderId);
    }

    /** Отмена указанного заказа
     *
     * @param $kfssOrderId
     * @return array|bool|mixed
     */
    public function cancel($kfssOrderId)
    {
        $fUser = \Yii::$app->shop->shopFuser;

        if ($response = $this->_call('DELETE', 'order/' . $kfssOrderId)) {
            if ($fUser->external_order_id == $kfssOrderId) {
                $fUser->external_order_id = null;
                $this->_externalOrderId = null;
                if (!$fUser->save()){
                    \Yii::error("Error save fUser: " . var_export($fUser->getErrors(), true), __METHOD__);
                }else{
                    return true;
                }
            }
        }

        return false;
    }

    /** Инициализация перерасчета скидок и акций. Если есть адрес - считается и доставка
     *
     * @param $kfssOrderId
     * @return array|bool|mixed
     */
    public function initRecalculateOrder($kfssOrderId)
    {
        return $this->_call('PUT', 'ordercalcs/' . $kfssOrderId);
    }

    public function getPromos()
    {
        $keyCache = 'remote_promos_list';

        $promos = \Yii::$app->cache->getOrSet($keyCache, function () {
            return $this->_call('GET', 'marketingaction');
        }, MIN_1);

        return $promos;
    }

    public function checkoutOrder($order)
    {
        return \Yii::$app->kfssApiV3->checkoutComplete(Order::getCheckoutCompleteData($order));
    }

    /** Завершение оформления заказа
     *
     * @param $data
     * @return array|bool|mixed
     */
    public function checkoutComplete($data)
    {
        return $this->_call('POST', 'ordersend', $data);
    }

    //* Комплексные методы *//

    //* /Комплексные методы *//

    //========================================================//

    /**
     *  Set promo coupon. Alternative - use promoBatch()
     * @param $data
     * @return array|bool|mixed|null
     */
    public function setCoupon($data)
    {
        return $this->_call('POST', 'promo/', $data);
    }

    /** Устанавливанием адрес заказа (доставки)
     *
     * @param $kfssOrderId
     * @param $data
     * @return array|bool|mixed
     */
    public function setDeliveryAddress($kfssOrderId, $data)
    {
        return $this->_call('PUT', 'orderaddress/' . $kfssOrderId, $data);
    }

    /** Получить адрес заказа(доставки) из КФСС
     *
     * @param $kfssOrderId
     * @return array|bool|mixed
     */
    public function getDeliveryAddress($kfssOrderId)
    {
        return $this->_call('GET', 'orderaddress/' . $kfssOrderId);
    }

    /** Устанавливаем тип доставки, курьерку, пвз
     *
     * Метод вызывает так же и перерасчет доставки, а не только изменение типа как такового
     *
     * @param $kfssOrderId
     * @param null $data
     * @return array|bool|mixed
     */
    public function setDeliveryType($kfssOrderId, $data = null)
    {
        return $this->_call('PUT', 'orderdelivery/' . $kfssOrderId, $data);
    }

    /** Устанавливаем тип оплаты
     *
     * @param $kfssOrderId
     * @param int $paymentTypeId
     * @return array|bool|mixed
     */
    public function setPaymentType($kfssOrderId, $paymentTypeId)
    {
        $data = [
            'orderId' => $kfssOrderId,
            'payTypeId' => $paymentTypeId
        ];

        return $this->_call('POST', 'orderpaytype', $data);
    }

    /** Пересчет данных по доставке - это метод смены типа доставки без указания параметров
     * (например для пересчета после смены адреса доставки)
     *
     * !!! Так же пересчет доставки может производиться при инициализации пересчета акций/скидок по заказу, нужен только заполенный адрес
     *
     * @param null $kfssOrderId
     * @return array|bool|mixed
     */
    public function initRecalculateDelivery($kfssOrderId = null)
    {
        return $this->setDeliveryType($kfssOrderId);
    }

    //* ПЕРЕРАСЧЕТЫ *//

    public function addCoupon($coupon)
    {
        //Купон может быть применен только к уже имеющемуся заказу
        if (!$canManageOrder = $this->canManageOrder()) {
            $initResponse = $this->initCartBatch();
            if (!empty($initResponse['orderId'])) {
                $canManageOrder = true;
                $kfssOrderId = $initResponse['orderId'];
                $this->setKfssOrderId($kfssOrderId);
            }
        }

        return $canManageOrder ? $this->setCoupon(Order::getCouponData($coupon)) : [
            'isSuccess' => false,
            'message' => 'Ошибка при применении купона. Попробуйте повторить попытку позже',
        ];
    }

    /**
     * Пересчет заказа текущего фузера на основе данных заказа из АПИ КФСС
     *
     * @param null $orderKfss
     * @param bool $oneClick - использовать для пересчета товары в корзине типа 1 клик
     * @return bool
     */
    public function recalculateOrder($orderKfss = null, $oneClick = false)
    {
        $kfssOrderId = $this->_externalOrderId;
        if ($oneClick) {
            $shopBaskets = \Yii::$app->shop->shopFuser->getShopBasketsOneClick()->orderBy('id ASC')->all();
        } else {
            $shopBaskets = \Yii::$app->shop->shopFuser->getShopBaskets()->orderBy('id ASC')->all();
        }

//        \Yii::info("RecalcOrder #{$kfssOrderId}", 'common\components\kfssapiv3');

        if ($shopBaskets) {
            //$shopBaskets = \common\helpers\ArrayHelper::index($shopBaskets, 'id');

            if (!$orderKfss && !$kfssOrderId) {
                //error
                \Yii::error("RecalcOrder #{$kfssOrderId}. Ошибка! Нет заказа/номера заказа", 'common\components\kfssapiv3');
                return true;
            } else {
                if (!$orderKfss) {
                    \Yii::error('getOrderFullUpdated recalculateOrder', 'common\components\kfssapiv3');
//                    $orderKfss = $this->getOrderFullUpdated($kfssOrderId);
                    $orderKfss = $this->getOrderBatch($kfssOrderId);
                }

                $this->recalculateBaskets($shopBaskets, $orderKfss);
                $this->recalculateFuser($orderKfss);

                return true;
            }
        }

        return true;
    }

    public function recalculateBaskets($shopBaskets, $orderKfss = null)
    {
//        \Yii::info("RecalcBaskets", 'common\components\kfssapiv3');

        if (!$orderKfss) {
//            $orderKfss = $this->getOrder(__METHOD__);
            $orderKfss = $this->getOrderBatch();
        }

        $shopBasketsKfssId = [];

        /** @var ShopBasket $shopBasket */
        foreach ($shopBaskets as $shopBasket) {
            $this->recalculateBasket($shopBasket, $orderKfss);

            //Соберем все kfss_id товаров корзины для сравнения со списком товаров из КФСС. Возможно КФСС пришлет доп.товары (подарки)
            if ($shopBasket->product->kfss_id) {
                $shopBasketsKfssId[$shopBasket->id] = $shopBasket->product->kfss_id;
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
                    \Yii::info("Remove product $removedShopBasketId", 'common\components\kfssapiv3');
                    $this->removeProduct($removedShopBasketId);
                }
            }
        }

        return true;
    }

    public function recalculateBasket(ShopBasket $shopBasket, $orderKfss = null)
    {
        if (!$orderKfss) {
//            $orderKfss = $this->getOrder(__METHOD__);
            $orderKfss = $this->getOrderBatch();
        }

        //Возможно это добавление в корзину до инициализации обмена по КФСС АПИ
        if ($orderKfss) {
            /** @var Product $product */
            $product = $shopBasket->product;

//            $cmsContentElement = \common\lists\Contents::getContentElementById($product->id);
            //$shopProduct = ShopProduct::getInstanceByContentElement($cmsContentElement); //$parentElement Записываем либо цену предложения либо главного товара
//            $parentElement = $cmsContentElement->product;

//                    $kfssOffercntId = $shopBasket->cmsContentElement->kfss_id;
//            $kfssOffcntId = $cmsContentElement['kfss_id'];
            $kfssOffcntId = $product->kfss_id;

//            \Yii::error("RecalcBasket #{$shopBasket->id} [KFSS_ID = {$kfssOffcntId}]", 'common\components\kfssapiv3');

            if ($kfssOffcntId && !empty($orderKfss['positions']) && !empty($orderKfss['positions'][$kfssOffcntId])) {
                $kfssElement = $orderKfss['positions'][$kfssOffcntId];
//                \Yii::error($kfssElement, 'debug');

                //Скидки для товара
                $shopBasket->discount_price = $kfssElement['originalPrice'] - $kfssElement['price'];
                $shopBasket->discount_value = "";
                $shopBasket->discount_name = "";
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
                \Yii::error("RecalcBasket - No position for basket {$shopBasket->id}", 'common\components\kfssapiv3');
            }

            //$shopBasket->price = $shopProduct->basePrice();

//            $shopBasket->name = $parentElement->getLotName();
//            $shopBasket->main_product_id = $parentElement->id;
//            $shopBasket->currency_code = 'RUB';

            if (!$shopBasket->save()) {
                \Yii::error("RecalcBasket.Save Ошибки: " . print_r($shopBasket->getErrors(), true), 'common\components\kfssapiv3');
                return false;
            }
        } else {
            \Yii::error("RecalcBasket - No KFSS order", 'common\components\kfssapiv3');
        }

        return true;
    }

    //Если есть какая то скидка запишет ее в отдельную таблицу связанную с фузером
    public function recalculateFuser($orderKfss = false)
    {
        \Yii::info("RecalcFuser #" . \Yii::$app->shop->shopFuser->id);

        \Yii::$app->shop->shopFuser->link('site', \Yii::$app->cms->site);

//        ShopDiscount::fuserRecalculate(\Yii::$app->shop->shopFuser);

        if ($orderKfss) {
            //* Пересчет механизмами сайта не требуется, берем инфу из кфсс *//
            $shopFuserDiscount = $this->shopFuser->ssShopFuserDiscount;

            if(!$shopFuserDiscount) {
                $shopFuserDiscount = new SsShopFuserDiscount();
                $shopFuserDiscount->link('shopFuser', $this->shopFuser);
            }

            $shopFuserDiscount->discount_price = 0;
            $shopFuserDiscount->discount_name = "";
            $shopFuserDiscount->free_delivery_discount_id = null;

//        $this->shopFuserDiscount->discount_price = 300;
//        $this->shopFuserDiscount->discount_name = "Скидка за общую сумму корзины";
//        $this->shopFuserDiscount->free_delivery_discount_id = null;

            if (!empty($orderKfss)) {
                $orderOriginalSum = $orderKfss['originalSum'] ?? 0;
                $orderSum = $orderKfss['sum'] ?? 0;

                if (!empty($orderKfss['discounts'])){
                    $discountNames = array_column($orderKfss['discounts'], 'name');
                    $shopFuserDiscount->discount_name = implode(' + ', $discountNames);
                }
            }

            //Если  из кфсс пришлаи скидки - то запишутся они, если ничего не пришло, запишется пустота (или сбросится до пустоты)
            //TODO Если приходит пустота то возможно все таки лучше удалять пустую запись, а не просто обнулять
            $shopFuserDiscount->save();

            return true;

            //* /Пересчет механизмами сайта не требуется, берем инфу из кфсс *//
        }

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
        \Yii::info("AddAdditionalProduct kfss_id=#{$productKfssId}", 'common\components\kfssapiv3');

        //Например сервисный сбор который приходит как товар имеет OffcntID = -5
        if ($productKfssId <= 0) {
            return true;
        }

        /** @var Product $product */
        $product = Product::find()->onlyModification()->where(['kfss_id' => $productKfssId])->one();
        if ($product) {
//            $orderKfss = $this->getOrder(__METHOD__);
            $orderKfss = $this->getOrderBatch();

            $kfssProduct = $orderKfss['positions'][$productKfssId];

            $productId = $product->id;
            $quantity = $kfssProduct['quantity'];

            /**
             * @var self $shopBasket
             */
            $shopBasket = ShopBasket::find()->where([
                'fuser_id' => \Yii::$app->shop->shopFuser->id,
                'product_id' => $productId,
                'order_id' => null,
            ])->one();

            if (!$shopBasket) {
                $shopBasket = new ShopBasket();

                $shopBasket->setAttributes([
                    'fuser_id' => \Yii::$app->shop->shopFuser->id,
                    'product_id' => $product->id,
                    'quantity' => 0,
                ]);

//                if ($asGift) {
//                    $shopBasket->setIsGift(true);
//                }
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
            \Yii::error("RecalcBaskets/AdditionalProductAdd.  Ошибка, не найден товар с kfss_id='{$productKfssId}'", 'common\components\kfssapiv3');
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
        \Yii::info("removeProduct shopBasketId=#{$shopBasketId}", 'common\components\kfssapiv3');

        /** @var CmsContentElement $product */
        $shopBasket = ShopBasket::findOne($shopBasketId);

        if ($shopBasket) {
            $shopBasket->has_removed = ShopBasket::HAS_REMOVED_TRUE;
            $shopBasket->save();
        } else {
            \Yii::error("RecalcBaskets/removeProduct.  Ошибка, не найден товар с shopBasketId='{$shopBasketId}'", 'common\components\kfssapiv3');
            return false;
        }

        return true;
    }

    //* /ПЕРЕРАСЧЕТЫ *//

    public function payOrder($kfssOrderId, $orderPayId, $bankId = 40)
    {
        $requestData = [
            "OrderId" => $kfssOrderId,
            "ExternalId" => $orderPayId,
            "BankId" => $bankId
        ];

        return $this->_call('POST', 'orderpay', $requestData);
    }

    /** Отмена указанного заказа
     *
     * @param $kfssOrderId
     * @return array|bool|mixed
     */
    public function lockStock($kfssOrderId)
    {
        //TODO Убрать когда условия будут не нужны
        $useLockstock = ShopOrder::isOnlinePaymentAllowed();

        if (!$useLockstock) {
            $responseData = [
                'isSuccess' => false,
                'message' => "Тестовый режим. Не используется"
            ];

            \Yii::error("[Time=0.0] Request kfssapiv3 [FAKE_PUT] lockstock/{$kfssOrderId}, data <EMPTY>, Response FAKE [200]: " . print_r($responseData ?: '<EMPTY>', true), 'common\components\kfssapiv3');

            return $responseData;
        }

        return $this->_call('PUT', 'lockstock/' . $kfssOrderId);
    }

    public function sendAbandonedCart($kfssOrderId)
    {
        return $this->_call('PUT', 'abandoncart/' . $kfssOrderId);
    }

    public function initOrderOneClick($params = false)
    {
        //Создаем базу заказа (данные клиента)
        //Добавляем позиции в заказ
        //Добавляем адрес доставки (по меньшей мере регион)
        //Добавляем тип доставки (необходимо только если у нас НЕ почта РФ, ибо она ставится по умолчанию)

        $fUser = \Yii::$app->shop->shopFuser;

        //Пока с каналами не густо не будем усложнять
        $saleChannelId = YII_ENV == 'prod' ? 4010 : 3820; //test

        if (!empty($params['source'])) {
            switch ($params['source']) {
                case ShopOrder::SOURCE_CPA:
                    $saleChannelId = 3810; //на бою и на деве совпало
                    break;
            }
        }

        //Подходит для 1клика
        $createOrderDefaults = [
            'sale_channel_id' => $saleChannelId,
            'client' => [
                'name' => 'ОдинКлик',
                'surname' => 'ОдинКлик',
                'patronymic' => 'ОдинКлик',
            ],
        ];
        $orderKfss = $this->create(Order::getCreateData($createOrderDefaults));

        if ($orderKfss && $orderKfss['orderId']) {
            $fUser->external_order_id = $orderKfss['orderId'];
            $this->_externalOrderId = $orderKfss['orderId'];
            $fUser->save();

            //Указываем состав корзины (1click ok)
            $baskets = $fUser->getShopBasketsOneClick()->all();
            $isPositionSet = $this->setPosition(Order::getPosition($baskets), $fUser->external_order_id);

            //Указываем адрес доставки (1click ok, но нужна дадата)
            $isDeliverySet = $this->setDeliveryAddress($fUser->external_order_id, Order::getDeliveryData($params['dadata'] ?? ''));

            //Указывам тип доставки (1click ok)
            $isDeliveryTypeSet = $this->setDeliveryType($fUser->external_order_id, Order::getDeliveryTypeData());

            //Указывам тип оплаты
            $isPaymentTypeSet = $this->setPaymentType($fUser->external_order_id, Order::getPaymentTypeData());

            return $orderKfss['orderId'];
        }

        return false;
    }

    private function _call($method, $url, $params = null)
    {
        $request = $this->httpClient->createRequest()
            ->setMethod($method)
            ->setFormat(Client::FORMAT_JSON)
            ->setUrl($url)
            ->setOptions([
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
            ])
//        ->setOptions([
//            'timeout' => self::TIMEOUT,
//        ])
//            ->setData($params)
            ->addHeaders(['Authorization' => 'Basic ' . base64_encode("{$this->username}:{$this->password}")]);

        if (!empty($params)) {
            $request->setData($params);
        } else {
            //Ибо ругается если нет данных и нет указания что их блина 0
            $request->addHeaders(['content-length' => 0]);
        }

        Common::startTimer("kfssapiv3::{$method}-{$url}");

        //На случай глюков и недоступности АПИ КФСС
        try {
            $response = $request->send();
        } catch (\Exception $e) {
//            \Yii::error("kfssApiV3::[{$method}]{$url}: " . $e->getMessage(), "kfssapiv3_exception");
            return false;
        }

        $responseData = $response->getData();

        $time = Common::getTimerTime("kfssapiv3::{$method}-{$url}", false);

        if ($time > self::TIMEOUT) {
            \Yii::error("kfssapiv3::[{$method}]{$url} time = {$time} sec", "kfssapiv3-long-response");
        }

        \Yii::error("[Time={$time}] Request kfssapiv3 [{$method}] {$url}, data " . print_r($params ?: '<EMPTY>', true) . ", Response [{$response->getStatusCode()}]: " . print_r($responseData ?: '<EMPTY>', true), 'common\components\kfssapiv3');

        if ($response->isOk) {
            //* Фикс кейса по нетипичномму статусу ответа от КФСС АПИ *//

            //Фикс ответа 200 вместо ожидаемого 204 при обращении на ordersend
            if ($url == 'ordersend' && $method == 'POST') {
                return $response->getStatusCode() == 200 ? true : $responseData;
            }
            //* /Фикс кейса по нетипичномму статусу ответа от КФСС АПИ *//

            return $response->getStatusCode() == 204 ? true : $responseData; //Учет кейса когда в ответ нет данных, только статус
        } else {
            \Yii::error("ERROR! Request kfssapiv3 [{$method}] {$url}, data " . print_r($params ?: '<EMPTY>', true) . ", Response [{$response->getStatusCode()}]: " . print_r($responseData ?: '<EMPTY>', true), 'common\components\kfssapiv3');
            return false;
        }
    }

    private function _cacheKey()
    {
        return 'kfss_order_' . $this->_externalOrderId;
    }

    public function _deliveryCacheKey($kfssOrderId = null)
    {
        $kfssOrderId = $this->getKfssOrderId($kfssOrderId);
        return $this->_externalOrderId ? "external_order_{$kfssOrderId}_delivery_price" : '';
    }

    public function isOnlinePaymentAllowed()
    {
        return (Setting::isOnlinePaymentAllowed() || \Yii::$app->session->get('op')) ? true : false;
    }

    public function setCookieLastRecalc()
    {
        return setcookie(self::LOCKSTOCK_TIME_COOCKIE_NAME, time(), time() + self::LOCKSTOCK_TIME, '/');
    }

    public function unsetCookieLastRecalc()
    {
        return setcookie(self::LOCKSTOCK_TIME_COOCKIE_NAME, '', time() - self::LOCKSTOCK_TIME, '/');
    }
}
