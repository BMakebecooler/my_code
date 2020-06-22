<?php

namespace common\components;

use common\helpers\Common;
use common\helpers\Order;
use common\models\cmsContent\CmsContentElement;
use common\models\Product;
use common\models\Setting;
use common\models\ShopOrder;
use modules\shopandshow\models\shop\ShopBasket;
use modules\shopandshow\models\shop\ShopDiscount;
use modules\shopandshow\models\shop\ShopProduct;
use skeeks\cms\base\Component;
use yii\di\Instance;
use yii\httpclient\Client;

/**
 * Класс работы с KFSS API
 * Class KfssApi
 * @package common\components
 */
class KfssApiV2 extends Component
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
        'creating'  => 1, //Создается
        'reserve'  => -4, //Блокировка, Резерв
    ];

    protected $debugMode = false;

    public static $order;

    public $isDisable = true;

    //Использовать ли только принудительный вариант работы (по явной, "кнопочной" инициализации)
    public $forcedUseOnly = true;

    private $_externalOrderId;

    public function init()
    {
        if (is_array($this->httpClient)) {
            $this->httpClient = Instance::ensure($this->httpClient);

            $this->httpClient->baseUrl = $this->baseUrl;
        }

        if (\common\helpers\App::isConsoleApplication()){
            $this->isDisable = false;

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

//        if (in_array($currentPhone, $phoneTester)) {
//            $this->isDisable = false;
//        }

        if (\Yii::$app->controller->id == 'v1/order') {
            $this->isDisable = false;
        }

//        $this->isDisable = false;

        //Для старой корзины отключаем любой способ работы с АПИ
        if (\Yii::$app->abtest->isC()){
            $this->forcedUseOnly = false;
        }

        parent::init();

    }

    /** Завершение оформления заказа
     *
     * @param $data
     * @return array|bool|mixed
     */
    public function checkoutComplete($data)
    {
        if ($this->isDisable && !$this->forcedUseOnly) {
            return false;
        }

        return $this->_call('POST', 'ordersend', $data);
    }

    /** Создание заказа
     *
     * @param $data
     * @return array|bool|mixed
     */
    public function create($data)
    {
        if ($this->isDisable && !$this->forcedUseOnly) {
            return false;
        }

        return $this->_call('POST', 'order', $data);
    }

    /** Отмена указанного заказа
     *
     * @param $kfssOrderId
     * @return array|bool|mixed
     */
    public function cancel($kfssOrderId)
    {
        if ($this->isDisable && !$this->forcedUseOnly) {
            return false;
        }

        return $this->_call('DELETE', 'order/' . $kfssOrderId);
    }

    /** Сообщаем в КФСС данные текущего состава корзины
     *
     * @param $data
     * @param $kfssOrderId
     * @return array|bool|mixed
     */
    public function setPosition($data, $kfssOrderId)
    {
        if ($this->isDisable) {
            return false;
        }

        //TODO Реализовать прорку товара на "подарок", который в КФСС передавать не надо.

        return $this->_call('PUT', 'orderposition/' . $kfssOrderId, $data);
    }

    /** Получение состава заказа из КФСС
     *
     * @param $kfssOrderId
     * @return array|bool|mixed
     */
    public function getPosition($kfssOrderId = null)
    {
        if ($this->isDisable && !$this->forcedUseOnly) {
            return false;
        }

        $kfssOrderId = !empty($kfssOrderId) && $kfssOrderId > 0 ? $kfssOrderId : $this->_externalOrderId;

        $orderPositions = $this->_call('GET', 'orderposition/' . $kfssOrderId);

        if ($orderPositions) {
            //Индексируем по ИД товара что упращения дальнейшего сравнения с составом корзины на сайте
            $orderPositions = \common\helpers\ArrayHelper::index($orderPositions, 'offcntId');
        }

        return $orderPositions;
    }

    /** Устанавливанием адрес заказа (доставки)
     *
     * @param $kfssOrderId
     * @param $data
     * @return array|bool|mixed
     */
    public function setDeliveryAddress($kfssOrderId, $data)
    {
        if ($this->isDisable) {
            return false;
        }

        return $this->_call('PUT', 'orderaddress/' . $kfssOrderId, $data);
    }

    /** Получить адрес заказа(доставки) из КФСС
     *
     * @param $kfssOrderId
     * @return array|bool|mixed
     */
    public function getDeliveryAddress($kfssOrderId)
    {
        if ($this->isDisable && !\Yii::$app->kfssApiV2->forcedUseOnly) {
            return false;
        }

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
        if ($this->isDisable) {
            return false;
        }

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
        if ($this->isDisable) {
            return false;
        }

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

    /** Получить данные по доставке (тип, цены, скидки, промо)
     *
     * @param $kfssOrderId
     * @return array|bool|mixed
     */
    public function getDelivery($kfssOrderId)
    {
        if ($this->isDisable && !$this->forcedUseOnly) {
            return false;
        }

        $response = $this->_call('GET', 'orderdelivery/' . $kfssOrderId);

        if (isset($response['price'])){
            \Yii::$app->session->set("external_order_{$kfssOrderId}_delivery_price", $response['price']);
        }

        return $response;
    }

    /** Получить данные по заказу (дата создания, статус, идКлиента, скидки, промо, суммы)
     *
     * С вызова этого метода начинается работа в любом направлении, сюда и определим проверку на необходимость пересоздания и тп
     *
     * @param $kfssOrderId
     * @return array|bool|mixed
     */
    public function getOrderMainData($kfssOrderId = null)
    {
        if ($this->isDisable && !$this->forcedUseOnly) {
            return false;
        }

        $kfssOrderId = !empty($kfssOrderId) && $kfssOrderId > 0 ? $kfssOrderId : $this->_externalOrderId;

        $responseData = $kfssOrderId ? $this->_call('GET', 'order/' . $kfssOrderId) : false;

        $needRecreateOrder = false;

        //Проверяем что в ответ пришел заказ
        if (!$responseData || empty($responseData['orderId'])) {
            $needRecreateOrder = true;
        }

        if($responseData && !empty($responseData['orderId'])){

            //Проверяем корректность даты (актуально для заказов в статусе Создается)
            if ($responseData['statusId'] == $this->kfssStatuses['creating'] || $responseData['statusId'] == $this->kfssStatuses['reserve']) {
                //Проверяем что дата заказа равна текущей (условие корректного обсчета в кфсс)
                $orderDateTs = !empty($responseData['createDate']) ? strtotime($responseData['createDate']) : time();
                if (date('Y-m-d') != date('Y-m-d', $orderDateTs)) {
                    $needRecreateOrder = true;
                }
            }else{
                //Статус отличен от Создается - надо пересоздавать что бы была возможность с ним работать
                $needRecreateOrder = true;
            }

        }

        if ($needRecreateOrder) {
            if ($kfssOrderId = $this->recreateOrder()){
                $responseData = $this->_call('GET', 'order/' . $kfssOrderId);
                if ($responseData && !empty($responseData['orderId'])){
                    $responseData['is_new_record'] = true; //Флаг-признак того что произошло не просто получение данных, а получение с предварительным (пере)созданием
                }
            }
        }

        //Проверяем что в ответ пришел заказ

        //Отключено так как оказалось неудачным местом, при первом запросе тут в заказе еще нет товаров
        if (false) {
            if ($responseData && !empty($responseData['orderId'])) {
                //Заказ точно если, проверяем для возможности онлайн платы
                $lockStockResponseData = $this->lockStock($responseData['orderId']);

                //FOR TESTS
                //TODO REMOVE AFTER TESTS
                //            $responseData['lockstock'] = 1;
                $responseData['lockstock'] = $lockStockResponseData['isSuccess'] ? (int)$lockStockResponseData['isSuccess'] : 0;
            }
        }


        return $responseData;
    }

    public function setCoupon($data)
    {
        if ($this->isDisable && !\Yii::$app->kfssApiV2->forcedUseOnly) {
            return false;
        }

        return $this->_call('POST', 'promo/', $data);
    }

    /** Инициализация перерасчета скидок и акций. Если есть адрес - считается и доставка
     *
     * @param $kfssOrderId
     * @return array|bool|mixed
     */
    public function initRecalculateOrder($kfssOrderId)
    {
        if ($this->isDisable) {
            return false;
        }

        return $this->_call('PUT', 'ordercalcs/' . $kfssOrderId);
    }

    /** Получить текущий статус заказа.
     *
     * @param $kfssOrderId
     * @return array|bool|mixed
     */
    public function getStatus($kfssOrderId = null)
    {
        if ($this->isDisable) {
            return false;
        }

        $kfssOrderId = !empty($kfssOrderId) && $kfssOrderId > 0 ? $kfssOrderId : $this->_externalOrderId;

        return $this->_call('GET', 'orderstatus/' . $kfssOrderId);
    }

    public function getPromos()
    {
        $keyCache = 'remote_promos_list';

        $promos = \Yii::$app->cache->getOrSet($keyCache, function () {
            return $this->_call('GET', 'marketingaction');
        }, MIN_1);

        return $promos;
    }

    //* Комплексные методы *//

    /** Инициализация заказа (для текущего фузера)
     *  создание (данные клиента), добавление позиций, добавление адреса доставки (если есть), типа доставки (если сразу не почта РФ)
     *
     * @return bool|mixed
     */
    function initOrder()
    {
        if ($this->isDisable && !$this->forcedUseOnly) {
            return false;
        }

        //Создаем базу заказа (данные клиента)
        //Добавляем позиции в заказ
        //Добавляем адрес доставки (по меньшей мере регион)
        //Добавляем тип доставки (необходимо только если у нас НЕ почта РФ, ибо она ставится по умолчанию)

        $fUser = \Yii::$app->shop->shopFuser;

        $orderKfss = $this->create(Order::getCreateData());

        if ($orderKfss && $orderKfss['orderId']) {
            $fUser->external_order_id = $orderKfss['orderId'];
            $this->_externalOrderId = $orderKfss['orderId'];
            $fUser->save();

            //Указываем состав корзины
            $baskets = $fUser->getShopBaskets()->all();
            $isPositionSet = $this->setPosition(Order::getPosition($baskets), $fUser->external_order_id);

            //Указываем адрес доставки
            $isDeliverySet = $this->setDeliveryAddress($fUser->external_order_id, Order::getDeliveryData());

            //Указывам тип доставки
            $isDeliveryTypeSet = $this->setDeliveryType($fUser->external_order_id, Order::getDeliveryTypeData());

            //Указывам тип оплаты
            $isPaymentTypeSet = $this->setPaymentType($fUser->external_order_id, Order::getPaymentTypeData());

            return $orderKfss['orderId'];
        }

        return false;
    }

    /** Получаем актуализированные данные по позициям заказа (актуальные = с предварительно пересчитанными акциями)
     *
     * @param null $kfssOrderId
     * @return array|bool|mixed
     */
    public function getPositionUpdated($kfssOrderId = null)
    {
        if ($this->isDisable) {
            return false;
        }

        $kfssOrderId = !empty($kfssOrderId) && $kfssOrderId > 0 ? $kfssOrderId : $this->_externalOrderId;

        //Инициируем обновление
        $isSuccess = $this->initRecalculateOrder($kfssOrderId);

        //Получаем обновленные данные
        if ($isSuccess) {
            $orderPositions = $this->getPosition($kfssOrderId);

            //Пересчет корзины в соответствии с пришедшими из апи данными
            //TODO Реализовать пересчет позиций корзины

            return $orderPositions;
        }
    }

    /** Получаем актуализированные данные по доставке заказа (актуальные = с пересчетом)
     * Данный метод требуется использовать при смене адреса досавки, но не типа, т.к. метод обновленния типа сразу вызывает перерасчет в КФСС
     * @param null $kfssOrderId
     * @return array|bool|mixed
     */
    public function getDeliveryUpdated($kfssOrderId = null)
    {
        if ($this->isDisable) {
            return false;
        }

        $kfssOrderId = !empty($kfssOrderId) && $kfssOrderId > 0 ? $kfssOrderId : $this->_externalOrderId;

        //Инициируем обновление
        $this->setDeliveryType($kfssOrderId, Order::getDeliveryTypeData());

        //Инициируем обновление
        $isSuccess = $this->initRecalculateOrder($kfssOrderId);

        //Получаем обновленные данные
        return $this->getDelivery($kfssOrderId);
    }

    /** Получение полных данных по заказу с инициированием обновления (пересчеты, не обновления данных)
     *
     * @param null $kfssOrderId
     * @return array|bool|mixed
     */
    public function getOrderFullUpdated($kfssOrderId = null)
    {
        if ($this->isDisable) {
            return false;
        }

        if (!empty(self::$order)) {
            \Yii::error('>> getOrderFullUpdated From Cache', 'common\components\kfssapiv2');
            return self::$order;
        }else{
            \Yii::error('>> getOrderFullUpdated FROM API', 'common\components\kfssapiv2');
        }

        $data = \Yii::$app->cache->get($this->_cacheKey());
        if (!($data === false)) {
            \Yii::error("GetOrderUpdated #{$kfssOrderId} from cache");
            return $data;
        }

        $kfssOrderId = !empty($kfssOrderId) && $kfssOrderId > 0 ? $kfssOrderId : $this->_externalOrderId;

        $kfssOrder = $this->getOrderMainData($kfssOrderId);

        if ($kfssOrder) {
            $kfssOrderId = $kfssOrder['orderId'];

            $kfssOrder['positions'] = $this->getPositionUpdated($kfssOrderId);
            $kfssOrder['orderAddress'] = $this->getDeliveryAddress($kfssOrderId);
            $kfssOrder['delivery'] = $this->getDeliveryUpdated($kfssOrderId);

            //* LOCKSTOCK *//

            $lockStockResponseData = $this->lockStock($kfssOrderId);

            //FOR TESTS
            //TODO REMOVE AFTER TESTS
//            $responseData['lockstock'] = 1;
            $kfssOrder['lockstock'] = $lockStockResponseData['isSuccess'] ? (int)$lockStockResponseData['isSuccess'] : 0;

            if (!$kfssOrder['lockstock'] && \Yii::$app->shop->shopFuser->pay_system_id == self::PAY_SYSTEM_ID_CARD){
                \Yii::$app->shop->shopFuser->setPayment(self::PAY_SYSTEM_ID_NAL, false);
            }

            //* /LOCKSTOCK *//

            self::$order = $kfssOrder;
            return $kfssOrder;
        }

        return false;
    }

    /** Получение полных данных по заказу БЕЗ обновлений (как есть в кфсс на данный момент)
     *
     * @param null $kfssOrderId
     * @return array|bool|mixed
     */
    public function getOrderFull($kfssOrderId = null)
    {
        if ($this->isDisable && !$this->forcedUseOnly) {
            return false;
        }

        if (!empty(self::$order)) {
            \Yii::error('>> getOrderFull From Cache', 'common\components\kfssapiv2');
            return self::$order;
        }else{
            \Yii::error('>> getOrderFull FROM API', 'common\components\kfssapiv2');
        }

        $data = \Yii::$app->cache->get($this->_cacheKey());
        if (!($data === false)) {
            \Yii::error("GetOrder #{$kfssOrderId} from cache");
            return $data;
        }

        $kfssOrderId = !empty($kfssOrderId) && $kfssOrderId > 0 ? $kfssOrderId : $this->_externalOrderId;

        $kfssOrder = $this->getOrderMainData($kfssOrderId);

        if ($kfssOrder) {
            $kfssOrderId = $kfssOrder['orderId'];

            $kfssOrder['positions'] = $this->getPosition($kfssOrderId);
            $kfssOrder['orderAddress'] = $this->getDeliveryAddress($kfssOrderId);
            $kfssOrder['delivery'] = $this->getDelivery($kfssOrderId);

            //* LOCKSTOCK *//

            $lockStockResponseData = $this->lockStock($kfssOrderId);

            //FOR TESTS
            //TODO REMOVE AFTER TESTS
//            $responseData['lockstock'] = 1;
            $kfssOrder['lockstock'] = $lockStockResponseData['isSuccess'] ? (int)$lockStockResponseData['isSuccess'] : 0;

            if (!$kfssOrder['lockstock'] && \Yii::$app->shop->shopFuser->pay_system_id == self::PAY_SYSTEM_ID_CARD){
                \Yii::$app->shop->shopFuser->setPayment(self::PAY_SYSTEM_ID_NAL, false);
            }

            //* /LOCKSTOCK *//

            self::$order = $kfssOrder;
            return $kfssOrder;
        }

        return false;
    }

    /** Пересоздание заказа в текущем состоянии для актуализации скидок/акций и тп с КФСС
     * $needRecalculate - DEPRECATED - пересчитываем на каждом вызове
     *
     * @param bool $needRecalculate
     * @return bool|mixed
     */
    public function recreateOrder($needRecalculate = false)
    {
        if ($this->isDisable && !$this->forcedUseOnly) {
            return false;
        }

        $oldkfssOrderId = $this->_externalOrderId;

        if ($this->_externalOrderId){
            $isCanceled = $this->cancel($this->_externalOrderId);
        }
        $kfssOrderId = $this->initOrder();

        \Yii::error("RecreateOrder. Canceled #{$oldkfssOrderId} -> Created #{$kfssOrderId}", 'common\components\kfssapiv2');

        //Приводим корзину в соответствие с заказом из АПИ
        $this->recalculateOrder();

        return $kfssOrderId;
    }

    /** Метод-заглушка в замен одноименного метода в КФСС АПИ в1
     *
     * @return array|bool|mixed|null
     */
    public function createOrder()
    {
        if ($this->isDisable) {
            return false;
        }

        $kfssOrderId = $this->initOrder();

        if ($kfssOrderId) {
            \Yii::error('getOrderFullUpdated createOrder', 'common\components\kfssapiv2');
            return $this->getOrderFullUpdated($kfssOrderId);
        }
        return null;
    }

    /** Метод-заглушка в замен одноименного метода в КФСС АПИ в1
     *
     * @param null $kfssOrderId
     * @return array|bool|mixed
     */
    public function getOrder($kfssOrderId = null)
    {
        if ($this->isDisable && !$this->forcedUseOnly) {
            return false;
        }

//        $kfssOrderId = $kfssOrderId ?: $this->_externalOrderId;
        $kfssOrderId = !empty($kfssOrderId) && $kfssOrderId > 0 ? $kfssOrderId : $this->_externalOrderId;

        if ($kfssOrderId) {

            //TODO Реализовать проверку на "устаревание" заказа в КФСС, что бы пересчитать в случае необходимости. Возможно добавить проверку на статус заказа.

            //TODO Избавиться от избыточности в получении данных с предварительным обновлением
            return $this->getOrderFull($kfssOrderId);
            //return \Yii::$app->request->isPjax ? $this->getOrderFull($kfssOrderId) : $this->getOrderFullUpdated($kfssOrderId);
        }
        return false;
    }

    /** Метод-заглушка в замен одноименного метода в КФСС АПИ в1
     *  Пока будем обновлять полный набор данных
     *
     * @param null $kfssOrderId
     * @return array|bool
     */
    public function updateOrder($kfssOrderId = null)
    {
        if ($this->isDisable) {
            return false;
        }

        $kfssOrderId = !empty($kfssOrderId) && $kfssOrderId > 0 ? $kfssOrderId : $this->_externalOrderId;

        //Получаем основные данные по заказу что бы убериться что все ок
        $orderKfss = $this->getOrderMainData($kfssOrderId);

        if ($orderKfss && !empty($orderKfss['orderId'])) {
            //Если заказа в КФСС не было по каким либо причинам (первое обращение, устаревание/отмена оригинального заказа) и произошло пересоздание,
            //то обновлять при этом еще раз безсмысленно
            if (!empty($orderKfss['is_new_record'])){
                return $this->getOrderFull($orderKfss['orderId']);
            }

            //Переприсваиваем так как getOrderMainData может пересоздать заказ и будет другой номер
            $kfssOrderId = $orderKfss['orderId'];

            $fUser = \Yii::$app->shop->shopFuser;

            //Указываем состав корзины
            $baskets = $fUser->getShopBaskets()->all();


            $isPositionSet = $this->setPosition(Order::getPosition($baskets), $kfssOrderId);

            //Указываем адрес доставки
            $isDeliverySet = $this->setDeliveryAddress($kfssOrderId, Order::getDeliveryData());

            //Указывам тип доставки
            $isDeliveryTypeSet = $this->setDeliveryType($kfssOrderId, Order::getDeliveryTypeData());

            //Указывам тип оплаты
            $isPaymentTypeSet = $this->setPaymentType($fUser->external_order_id, Order::getPaymentTypeData());

            //Данные заказа со всеми обновлениями
            \Yii::error('getOrderFullUpdated updateOrder', 'common\components\kfssapiv2');
            $orderKfssUpdated = $this->getOrderFullUpdated();

            return $orderKfssUpdated;
        }else{
            //Error
        }

        return false;
    }


    public function checkoutOrder($order){
        return \Yii::$app->kfssApiV2->checkoutComplete(Order::getCheckoutCompleteData($order));
    }

    //* /Комплексные методы *//

    //* ПЕРЕРАСЧЕТЫ *//

    public function addCoupon($coupon)
    {
        $kfssOrderId = !empty($kfssOrderId) && $kfssOrderId > 0 ? $kfssOrderId : $this->_externalOrderId;

        if ($this->isDisable && !\Yii::$app->kfssApiV2->forcedUseOnly) {
            return false;
        }

        //Если работаем ерез АПИ то заказ уже должен быть обязательно иначе купон не применится
        //Так что пробуем проверить наличие заказа и создаем если его нет
        $orderKfss = $this->getOrderMainData();
//        \Yii::$app->runAction("shopandshow/cart/recalculate-remote");

        return $this->setCoupon(Order::getCouponData($coupon));
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
        if ($this->isDisable) {
            return false;
        }

        $kfssOrderId = $this->_externalOrderId;
        if ($oneClick){
            $shopBaskets = \Yii::$app->shop->shopFuser->getShopBasketsOneClick()->orderBy('id ASC')->all();
        }else{
            $shopBaskets = \Yii::$app->shop->shopFuser->getShopBaskets()->orderBy('id ASC')->all();
        }

        \Yii::info("RecalcOrder #{$kfssOrderId}", 'common\components\kfssapiv2');

        if ($shopBaskets) {
            //$shopBaskets = \common\helpers\ArrayHelper::index($shopBaskets, 'id');

            if (!$orderKfss && !$kfssOrderId) {
                //error
                \Yii::error("RecalcOrder #{$kfssOrderId}. Ошибка! Нет заказа/номера заказа", 'common\components\kfssapiv2');
                return true;
            } else {
                if (!$orderKfss) {
                    \Yii::error('getOrderFullUpdated recalculateOrder', 'common\components\kfssapiv2');
                    $orderKfss = $this->getOrderFullUpdated($kfssOrderId);
                }

                $this->recalculateBaskets($shopBaskets, $orderKfss);
                $this->recalculateFuser();

                return true;
            }
        }

        return true;
    }

    public function recalculateBaskets($shopBaskets, $orderKfss = null)
    {
//        \Yii::info("RecalcBaskets", 'common\components\kfssapiv2');

        if (!$orderKfss) {
            $orderKfss = $this->getOrder(__METHOD__);
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
                    \Yii::info("Remove product $removedShopBasketId", 'common\components\kfssapiv2');
                    $this->removeProduct($removedShopBasketId);
                }
            }
        }

        return true;
    }

    public function recalculateBasket($shopBasket, $orderKfss = null)
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

            \Yii::info("RecalcBasket #{$shopBasket->id} [KFSS_ID = {$kfssOffcntId}]", 'common\components\kfssapiv2');

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
                \Yii::error("RecalcBasket - No position for basket {$shopBasket->id}", 'common\components\kfssapiv2');
            }

            //$shopBasket->price = $shopProduct->basePrice();

            $shopBasket->name = $parentElement->getLotName();
            $shopBasket->main_product_id = $parentElement->id;

            $shopBasket->currency_code = 'RUB';

            if (!$shopBasket->save()) {
                \Yii::error("RecalcBasket.Save Ошибки: " . print_r($shopBasket->getErrors(), true), 'common\components\kfssapiv2');
                return false;
            }
        } else {
            \Yii::error("RecalcBasket - No KFSS order", 'common\components\kfssapiv2');
        }

        return true;
    }

    public function recalculateFuser()
    {
        \Yii::info("RecalcFuser #" . \Yii::$app->shop->shopFuser->id);

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
        \Yii::info("AddAdditionalProduct kfss_id=#{$productKfssId}", 'common\components\kfssapiv2');

        //Например сервисный сбор который приходит как товар имеет OffcntID = -5
        if ($productKfssId <= 0) {
            return true;
        }

        /** @var CmsContentElement $product */
        $product = CmsContentElement::find()->where(['kfss_id' => $productKfssId])->andWhere(['content_id' => Product::MOD])->one();
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
            \Yii::error("RecalcBaskets/AdditionalProductAdd.  Ошибка, не найден товар с kfss_id='{$productKfssId}'", 'common\components\kfssapiv2');
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
        \Yii::info("removeProduct shopBasketId=#{$shopBasketId}", 'common\components\kfssapiv2');

        /** @var CmsContentElement $product */
        $shopBasket = ShopBasket::findOne($shopBasketId);

        if ($shopBasket) {
            $shopBasket->has_removed = ShopBasket::HAS_REMOVED_TRUE;
            $shopBasket->save();
        } else {
            \Yii::error("RecalcBaskets/removeProduct.  Ошибка, не найден товар с shopBasketId='{$shopBasketId}'", 'common\components\kfssapiv2');
            return false;
        }

        return true;
    }

    //* /ПЕРЕРАСЧЕТЫ *//

    public function payOrder($kfssOrderId, $orderPayId, $bankId = 40){
        $requestData = [
            "OrderId"    => $kfssOrderId,
            "ExternalId" => $orderPayId,
            "BankId"     => $bankId
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
        if ($this->isDisable && !$this->forcedUseOnly) {
            return false;
        }

        //TODO Убрать когда условия будут не нужны
        $useLockstock = $this->isOnlinePaymentAllowed();

        if (!$useLockstock){
            $responseData = [
                'isSuccess' => false,
                'message' => "Тестовый режим. Не используется"
            ];

            \Yii::error("[Time=0.0] Request kfssApiV2 [FAKE_PUT] lockstock/{$kfssOrderId}, data <EMPTY>, Response FAKE [200]: " . print_r($responseData?: '<EMPTY>', true), 'common\components\kfssapiv2');

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

        if (!empty($params['source'])){
            switch ($params['source']){
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
        if ($this->isDisable && !$this->forcedUseOnly) {
            return false;
        }

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
        }else{
            //Ибо ругается если нет данных и нет указания что их блина 0
            $request->addHeaders(['content-length' => 0]);
        }

        Common::startTimer("kfssApiV2::{$method}-{$url}");

        $response = $request->send();
        $responseData = $response->getData();

        $time = Common::getTimerTime("kfssApiV2::{$method}-{$url}", false);

        if ($time > self::TIMEOUT){
            \Yii::error("kfssApiV2::[{$method}]{$url} time = {$time} sec", "kfssapiv2-long-response");
        }

        \Yii::error("[Time={$time}] Request kfssApiV2 [{$method}] {$url}, data " . print_r($params ?: '<EMPTY>', true) . ", Response [{$response->getStatusCode()}]: " . print_r($responseData?: '<EMPTY>', true), 'common\components\kfssapiv2');

        if ($response->isOk) {
            //* Фикс кейса по нетипичномму статусу ответа от КФСС АПИ *//

            //Фикс ответа 200 вместо ожидаемого 204 при обращении на ordersend
            if ($url == 'ordersend' && $method == 'POST'){
                return $response->getStatusCode() == 200 ? true : $responseData;
            }
            //* /Фикс кейса по нетипичномму статусу ответа от КФСС АПИ *//

            return $response->getStatusCode() == 204 ? true : $responseData; //Учет кейса когда в ответ нет данных, только статус
        } else {
            \Yii::error("ERROR! Request kfssApiV2 [{$method}] {$url}, data " . print_r($params ?: '<EMPTY>', true) . ", Response [{$response->getStatusCode()}]: " . print_r($responseData?: '<EMPTY>', true), 'common\components\kfssapiv2');
            return false;
        }
    }

    private function _cacheKey()
    {
        return 'kfss_order_' . $this->_externalOrderId;
    }

    /**
     * @deprecated
     * @return bool
     */
    public function isOnlinePaymentAllowed(){
        return (Setting::isOnlinePaymentAllowed() || \Yii::$app->session->get('op')) ? true : false;
    }

    public function setCookieLastRecalc(){
        return setcookie(self::LOCKSTOCK_TIME_COOCKIE_NAME, time(), time() + self::LOCKSTOCK_TIME, '/');
    }

    public function unsetCookieLastRecalc(){
        return setcookie(self::LOCKSTOCK_TIME_COOCKIE_NAME, '', time() - self::LOCKSTOCK_TIME, '/');
    }
}
