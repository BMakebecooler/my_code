<?php
/**
 * @property GuidBehavior $guid
 *
 * @property string $publicUrl
 *
 */

namespace common\models;


use common\helpers\Api;
use common\helpers\ArrayHelper;
use common\helpers\Developers;
use common\helpers\Strings;
use common\helpers\Common;
use common\helpers\User AS UserHelper;
use Exception;
use modules\shopandshow\models\shop\ShopBuyer;
use modules\shopandshow\models\shop\forms\QuickOrder;
use modules\shopandshow\behaviors\TimestampBehavior as SsTimestampBehavior;
use modules\shopandshow\models\shop\ShopOrder2discountCoupon;
use yii\behaviors\TimestampBehavior;
use modules\shopandshow\models\common\GuidBehavior;
use yii\helpers\Url;
use yii\httpclient\Client;

class ShopOrder extends generated\models\ShopOrder
{
    const SERVICE_CHARGE_PRICE = 50;

    const SOURCE_UNKNOWN = 'unknown';
    const SOURCE_ALL = 'all';
    const SOURCE_SITE = 'site';
    const SOURCE_SITE_KFSS = 'site_kfss';
    const SOURCE_BITRIX = 'bitrix';
    const SOURCE_KFSS = 'kfss';
    const SOURCE_MOBILE_APP = 'mobile_app';
    const SOURCE_CPA = 'cpa';

    const SOURCE_DETAIL_UNKNOWN = 'unknown';
    const SOURCE_DETAIL_ALL = 'all';
    const SOURCE_DETAIL_SITE = 'site';
    const SOURCE_DETAIL_MOBILE = 'mobile';
    const SOURCE_DETAIL_PHONE1 = 'phone_1'; // 8 800 7755665
    const SOURCE_DETAIL_PHONE2 = 'phone_2'; // 8 800 3016010
    const SOURCE_DETAIL_ONE_CLICK = 'one_click';
    const SOURCE_DETAIL_ONE_CLICK_MOBILE = 'one_click_mobile';
    const SOURCE_DETAIL_FAST_ORDER = 'fast_order';
    const SOURCE_DETAIL_FAST_ORDER_MOBILE = 'fast_order_mobile';
    const SOURCE_DETAIL_FAST_ORDER_MOBILE_APP = 'fast_order_mobile_app';
    const SOURCE_DETAIL_CPA_KMA = 'cpa_kma';
    const SOURCE_DETAIL_CPA_ADMITAD = 'cpa_admitad';

    public static $sourceLabels = [
        'main' => [
            self::SOURCE_UNKNOWN => 'Неизвестно',
            self::SOURCE_ALL => 'Все',
            self::SOURCE_SITE => 'Сайт',
            self::SOURCE_SITE_KFSS => 'Сайт (КФСС)',
            self::SOURCE_KFSS => 'КФСС',
            self::SOURCE_BITRIX => 'Битрикс',
            self::SOURCE_MOBILE_APP => 'Моб.приложение',
            self::SOURCE_CPA => 'Лендинг',
        ],
        'detail' => [
            self::SOURCE_DETAIL_UNKNOWN => 'Неизвестно',
            self::SOURCE_DETAIL_ALL => 'Все',
            self::SOURCE_DETAIL_SITE => 'Десктоп',
            self::SOURCE_DETAIL_MOBILE => 'Мобильный',
            self::SOURCE_DETAIL_PHONE1 => 'Тел 8-800-775-5665',
            self::SOURCE_DETAIL_PHONE2 => 'Тел 8-800-301-6010',
            self::SOURCE_DETAIL_ONE_CLICK => 'Заказы 1 клик (десктоп)',
            self::SOURCE_DETAIL_ONE_CLICK_MOBILE => 'Заказы 1 клик (мобильные)',
            self::SOURCE_DETAIL_FAST_ORDER => 'Быстрый заказ (десктоп)',
            self::SOURCE_DETAIL_FAST_ORDER_MOBILE => 'Быстрый заказ (мобильные)',
            self::SOURCE_DETAIL_FAST_ORDER_MOBILE_APP => 'Быстрый заказ (моб.приложение)',
        ],
    ];

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
                [['payed', 'canceled', 'allow_delivery', 'update_1c', 'deducted', 'marked', 'reserved', 'external_order'], 'default', 'value' => Common::BOOL_N],
                [['recount_flag'], 'default', 'value' => Common::BOOL_Y],
                [['currency_code'], 'default', 'value' => 'RUB'],
                [['key'], 'default', 'value' => \Yii::$app->security->generateRandomString()],
                [['payed', 'canceled', 'allow_delivery', 'update_1c', 'deducted', 'marked', 'reserved', 'external_order'], 'default', 'value' => Common::BOOL_N],
                [['recount_flag'], 'default', 'value' => Common::BOOL_Y],
                [['status_code'], 'default', 'value' => ShopOrderStatus::STATUS_CODE_START],
                [['status_at'], 'default', 'value' => \Yii::$app->formatter->asTimestamp(time())],
                [['currency_code'], 'default', 'value' => \Yii::$app->money->currencyCode],
                [['site_id'], 'default', 'value' => \Yii::$app->cms->site->id],
            ]
        );
    }


    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors = ArrayHelper::merge(parent::behaviors(), [
            GuidBehavior::className() => GuidBehavior::className()
        ]);

        unset($behaviors[TimestampBehavior::className()]);

        $behaviors[SSTimestampBehavior::className()] = [
            'class' => SSTimestampBehavior::className()
        ];

        return $behaviors;
    }

    /**
     *
     * @param $status
     */
    public function setStatus($status)
    {
        $this->status_code = $status;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(ShopOrderStatus::className(), ['code' => 'status_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getIsSourceCpa()
    {
        return $this->source == self::SOURCE_CPA;
    }

    /**
     * @return string
     */
    public function getPublicUrl($scheme = true)
    {
        return Url::to(['/shop/order/finish', 'key' => $this->key], $scheme);
    }

    /**
     * @param $checkoutPhone - доп телефон для баера
     * @param $type - basket type
     * @return array
     * @return int $type
     * @throws Exception
     */
    static public function checkout($checkoutPhone = null, $type = ShopBasket::TYPE_DEFAULT)
    {
        $response = [
            'success' => true,
            'message' => '',
            'data' => [],
        ];

        //Получение/создание пользователя со связанным баером и записью связей между всем этим добром в фузере
        $userData = self::getCheckoutUser();
        $user = $userData['success'] && $userData['data'] ? $userData['data'] : null;

        \Yii::error("Checkout userId: " . ($user ? $user->id : 'EMPTY'));

        if (!$user){
            return $userData;
        }

        /**
         * @var $shopFuser \modules\shopandshow\models\shop\ShopFuser
         */
        $shopFuser = \Yii::$app->shop->shopFuser;
        $shopFuser->loadDefaultValues();
        $shopBuyer = $shopFuser->getBuyer()->one();

        //Создание покупателя в зависимости от выбранного типа
        //[DEPRECATED Исправлено на универсальный вариант] Проверяет авторизованного пользователя!
//        $shopBuyer = $shopFuser->createModelShopBuyer();

        if (
            $shopFuser->validate() &&
            $shopBuyer->validate()
        ) {
            /**
             * Сохраняем доп телефон
             */
            if ($checkoutPhone) {
                $shopBuyer->relatedPropertiesModel->setAttribute('phone', $checkoutPhone);
                $shopBuyer->relatedPropertiesModel->save(false);
            }

            try {
                if ($type == ShopBasket::TYPE_DEFAULT) {
                    $newOrder = self::createOrder($shopFuser);
                } elseif ($type == ShopBasket::TYPE_ONE_CLICK) {
                    $newOrder = self::createOrderOneClick($shopFuser);
                } else {
//                    throw new Exception('Не известный тип заказа!');
                }
//                \Yii::error('Order_number: ' . $newOrder->order_number . ' Order_guid: '. $newOrder->guid_id, 'checkoutLog');
//                \Yii::error("orderId: {$newOrder->id}, Order_number: {$newOrder->order_number}, Order_guid:  {$newOrder->guid_id}", 'checkoutLog');
                if ($newOrder) {
                    //Оплачиваемые заказы через КЦ не гоняем
                    if ($newOrder->pay_system_id == \Yii::$app->kfssApiV3::PAY_SYSTEM_ID_CARD && self::isOnlinePaymentAllowed()) {
                        //TODO Написать Текс СМС для заказов без подтверждения КЦ
                        $text = 'Ваш заказ принят. Ожидайте дальнейших уведомлений по статусу Вашего заказа.';
//                        \Yii::info('Send sms for skill user', 'checkoutLog');
                    } else {
                        $text = 'Ваш заказ принят. Наши операторы свяжутся с Вами для уточнения деталей заказа.';
                    }

                    //Отправка смс что создался заказ
                    //\common\helpers\Order::sendSmsCreateOrder($newOrder, $text);

//                    $userEmail = Survey::getUserEmail($newOrder->user);
                    $userEmail = false;

                    //Отправляем письмо если понятно куда и если есть номер заказа
                    if (false && $userEmail && $newOrder->order_number){
                        \common\helpers\Order::sendEmailCreateOrder(
                            $newOrder,
                            "Вы оформили заказ №{$newOrder->order_number} на Shop&Show.",
                            'modules/shop/client_new_order'
                        );
                    }

                    //Опросник
//                    Survey::sendSurvey(Survey::ORDER_FINISH_TYPE, $newOrder);

                    //Оплачиваемые заказы в КФСС не отправляем на данном этапе
                    //ОТКЛЮЧЕНО ДЛЯ ТЕСТОВ С ЭМУЛЯЦИЕЙ ОПЛАТЫ! TODO ВЕРНУТЬ!
//                    if (false && $newOrder->pay_system_id == \Yii::$app->kfssApiV2::PAY_SYSTEM_ID_CARD){ //Для теста
                    if ($newOrder->pay_system_id == \Yii::$app->kfssApiV3::PAY_SYSTEM_ID_CARD && self::isOnlinePaymentAllowed()){ //Для боя
                        $newOrder->do_not_need_confirm_call = Common::BOOL_Y_INT;
                        $newOrder->save();

                        //инициируем и переводим на оплату

                        //[DEPRECATED] Сбер
                        if (false) {
                            if (self::isOnlinePaymentAllowed()) { //Оплата только для избранных //TODO Убрать при переходе в полный боевой режим
                                $payFormUrl = \Yii::$app->sberApi->createPaymentOrder($newOrder->id, ($newOrder->price + $newOrder->price_delivery + 50) * 100, $newOrder->publicUrl);
                                if ($payFormUrl) {
                                    //Так как вызывается в аяксе и надо как то вернуть путь к форме - передаем как коммент. Так себе варик.
                                    $newOrder->comments = $payFormUrl;
                                    $newOrder->save();
                                }
                            }
                        }

                        //КФСС Альфа
                        if (true) {
                            if (self::isOnlinePaymentAllowed()) { //Оплата только для избранных  или по факту включения такой возможности для всех
                                $payFormUrl = \Yii::$app->kfssAlfaApiV1->registerOrderPayment($newOrder);
                                if ($payFormUrl) {
                                    //Так как вызывается в аяксе и надо как то вернуть путь к форме - передаем как коммент. Так себе варик.
                                    $newOrder->comments = $payFormUrl;

                                    if (!$newOrder->save()){
                                        \Yii::error(var_export($newOrder->getErrors(), true), 'checkoutLog');
                                    }else{
                                        //Оплата зарегистрирована, оформляем заказ
                                        /** @var Client $responseData */
                                        $responseData = \Yii::$app->kfssApiV3->checkoutOrder($newOrder);

                                        if ($responseData && $responseData === true) {
                                            //Если запрос прошел нормально - выставляем статус что заказ пришел в удаленную систему
                                            $newOrder->setStatus(ShopOrderStatus::STATUS_SUCCESS);
                                            $newOrder->save();
                                        } else {
                                            \Yii::error("Ошибка подтверждения предоплаченного заказа в КФСС. Заказ №{$newOrder->id} / КФСС №{$newOrder->order_number}", 'checkoutLog');
                                            \Yii::error("Response №{$newOrder->order_number}: " . var_export($responseData, true), 'checkoutLog');
                                        }
                                    }
                                }
                            }
                        }

                    }else{
                        //ДЛЯ ТЕСТОВ, ЭМУЛЯЦИЯ ОПЛАТЫ! УБРАТЬ ПОСЛЕ ТЕСТОВ!!!

                        if (false) { //Фейковая оплата - включить во время тестов
                            if ($newOrder->pay_system_id == \Yii::$app->kfssApiV3::PAY_SYSTEM_ID_CARD && self::isOnlinePaymentAllowed()) {
                                //orderPayId = 'ff86094c-66ea-7055-9070-dc9504b272e3'
                                $orderPayId = 'ff86094c-66ea-7055-9070-' . (substr(md5(time()), 0, 12));
                                \Yii::$app->kfssApiV3->payOrder($newOrder->order_number, $orderPayId);
                            }
                        }

                        // /ДЛЯ ТЕСТОВ, ЭМУЛЯЦИЯ ОПЛАТЫ! УБРАТЬ ПОСЛЕ ТЕСТОВ!!!

                        /** @var Client $responseData */
                        $responseData = \Yii::$app->kfssApiV3->checkoutOrder($newOrder);

                        if ($responseData && $responseData === true) {
                            //Если запрос прошел нормально - выставляем статус что заказ пришел в удаленную систему
                            $newOrder->setStatus(ShopOrderStatus::STATUS_SUCCESS);
                            $newOrder->save();
                        } else {
                            \Yii::error("Ошибка подтверждения заказа в КФСС. Заказ №{$newOrder->id} / КФСС №{$newOrder->order_number}", 'checkoutLog');
                            \Yii::error("Response №{$newOrder->order_number}: " . var_export($responseData, true), 'checkoutLog');
                        }
                    }

                } else {
//                    \Yii::error('Не получилось создать заказ 4' . print_r($newOrder->getErrors(), true), 'checkoutLog');
                    \Yii::error('Не получилось создать заказ 4', 'checkoutLog');
                    $response['success'] = false;
                    $response['message'] = "Не получилось создать заказ 4";
                }

                $response['data'] = $newOrder;

            } catch (\Exception $e) {

//                Developers::reportProblem('Ошибка в заказе 3' . PHP_EOL . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
                \Yii::error('Ошибка в заказе 3' . PHP_EOL . $e->getMessage() . PHP_EOL . $e->getTraceAsString(), 'checkoutLog');
                $response['success'] = false;
            }
        }else{
            \Yii::error('Не получилось создать заказ 5. 1)' . print_r($shopFuser->getErrors(), true) . ' | 2)' . print_r($shopBuyer->getErrors(), 'checkoutLog'));
        }

        return $response;
    }

    public static function checkoutOneClick($productId, $phone, $dadata, $source = ShopOrder::SOURCE_SITE)
    {
        $debug = false;
        $needRedirect = false;
        $success = false;
        $message = '';
        $data = [];

        $fuser = \Yii::$app->shop->shopFuser;

        $isKfssApiDisabled = \Yii::$app->kfssApiV2->isDisable;

        //Отключаем отключение
        if ($isKfssApiDisabled /*&& !empty($orderNumber)*/){
            \Yii::$app->kfssApiV2->isDisable = false;
        }

        $phone = Strings::getPhoneClean($phone);

        //Если есть связаный заказ в КФСС, то это обычный (пересчитанный) заказ, дабы не путать обычный заказ с 1 кликом запомним обычный заказ что бы его потом восстановить
        $kfssOrderIdNormal = $fuser->external_order_id ?: '';
//        $kfssOrderNormalCoupons = $fuser->discount_coupons;

        $quantity = max(1, \Yii::$app->request->post('quantity', 1));

        //QuickOrder - прослойка между гостем и зареганым узером.
        //Если авторизованный - все понятно. Если гость - ищем по телефону, если не нашелся - регаем
//        $modelUser = UserHelper::isAuthorize() ? \common\models\user\User::findOne(\Yii::$app->user->id) : new QuickOrder();

        //* Определяем юзера *//

        if (UserHelper::isAuthorize()){
            $modelUser = \common\models\user\User::findOne(\Yii::$app->user->id);
        }else{
            $userFound = User::findByPhone($phone);
            if ($userFound){
                $modelUser = $userFound;
            }else{
                $modelUser = new QuickOrder();
                $modelUser->phone = $phone;
            }
        }

        //* /Определяем юзера *//

        //Телефон есть, можем продолжать
        if ($phone){
            //В любом случае как текущий телефон пишем в фузера
            $affected = $fuser->savePhone($phone);

//            $user = $modelUser instanceof \common\models\user\User ? $modelUser : $modelUser->signup();

            //Сохраняем юзера (имеющегося или нового)
            $userOk = false;
            if ($modelUser instanceof \common\models\user\User || $modelUser instanceof User){
                $userOk = true;
                $user = $modelUser;
            }else{
                if ($registeredUser = $modelUser->signup()){
//                    \Yii::$app->user->login($registeredUser, DAYS_30);
//                    $modelUser->storeLoginAttempt();
                    $userOk = true;
                    $user = $registeredUser;
                }else{
                    \Yii::error("ERROR! Can't save new user! Phone: {$modelUser->phone}", __METHOD__);
                }
            }

            //Возможно например клиент заблокирован, так что проверяем что все ок

            if (!empty($user)) {

                //Для одного клика это похоже не особо обязательно, так что пока не делаем
                if (false) {
                    if (!$fuser->user_id && !empty($user->id)) {
                        $fuser->user_id = $user->id;
                        $fuser->save();
                    }
                }

                //Или уже норм юзер или зареганый гость
                //Если нет номера телефона - то сохраним. Если уже есть - у пользователя обновлять не требуется
                if (!$user->phone) {
                    $user->phone = $phone;
                    $user->save();
                }

//              $user = \Yii::$app->user->identity;
                //Для 1 клика баер и ненужен вовсе
                if (true) {
                    $buyer = $fuser->buyer;
                    if (!$buyer && !empty($user->id) /*&& UserHelper::isAuthorize()*/) {
                        $buyer = new ShopBuyer([
                            'shop_person_type_id' => 1, //1 - физ лицо. Из-за обновления моделей не используем констарнты из старых
                            'cms_user_id' => $user->id
                        ]);
                        $buyer->save();
                        $fuser->buyer_id = $buyer->id;
                    }
                }
                $fuser->delivery_id = 5;
                $fuser->pay_system_id = 1;
                $fuser->save();

                //* Добавляем товар в корзину *//

                $productAdd = ShopBasket::addOneClick($productId, $quantity, $source);

                //* /Добавляем товар в корзину *//

                if ($debug) {
                    var_dump($productAdd);
                }
                if (!$productAdd['success']) {
                    //Чтото пошло не так
                    $message = 'Ошибка при добавлении товара в корзину. ' . $productAdd['message'];
                } else {

                    //Товар в корзине есть, данные региона (дадата) есть, телефон есть
                    //Создаем новый заказ

                    //Перед формированием заказа необходимо заопрувить от кфсс что все ОК и заказ таки можно оформлять
                    //К тому же возможны какие то комментарии/правки от кфсс

                    //* КФСС Пересчет *//

                    $kfssOrderId = \Yii::$app->kfssApiV2->initOrderOneClick(['dadata' => $dadata, 'source' => $source]);

                    if ($kfssOrderId) {

                        \Yii::error("OrderOneClick. Hide real order  #{$kfssOrderIdNormal} -> CreatedOneClick #{$kfssOrderId}", __METHOD__);

                        $orderKfss = \Yii::$app->kfssApiV2->getOrderFullUpdated($kfssOrderId);

                        //Приводим корзину в соответствие с заказом из АПИ
                        \Yii::$app->kfssApiV2->recalculateOrder($orderKfss, true);

                        //* /КФСС Пересчет *//

                        //Проверяем что после синхронизаций с КФСС все ок, товар на месте

                        $basket = ShopBasket::find()
                            ->where([
                                'fuser_id' => $fuser->id,
                                'product_id' => $productId,
                                'type' => ShopBasket::TYPE_ONE_CLICK,
                                'has_removed' => Common::BOOL_N_INT
                            ])
                            ->one();

                        if (!$basket) {
                            $message = "Извините, товар закончился.";
                        } else {
                            $order = new ShopOrder();

                            $order->status_at = \Yii::$app->formatter->asTimestamp(time());

                            $order->user_id = $user->id;
                            $order->buyer_id = $fuser->buyer_id;

                            // источник заказа
                            $order->source = $source;
                            $order->source_detail = ShopOrder::SOURCE_DETAIL_ONE_CLICK; //Нормально и для сайтового 1клик и для лендингов

                            $order->person_type_id = 1; //УТОЧНИТЬ
                            $order->site_id = 1; //УТОЧНИТЬ
                            $order->delivery_id = $fuser->delivery_id; //УТОЧНИТЬ
                            $order->price_delivery = 2; //УТОЧНИТЬ
                            $order->pay_system_id = $fuser->pay_system_id; //УТОЧНИТЬ
                            $order->currency_code = 'RUB';

                            $order->price = 0;

                            if ($basket) {
                                $order->price = $basket->price;
                            }
                            //К строке из-за ругани валидации
                            $order->order_number = (string)$kfssOrderId;

                            if (!$order->save()) {
                                //                        var_dump('SAVE ORDER ERR');
                                //                        var_dump($order->getErrors());
                                $message = "Не удалось создать заказ. Попробуйте повторить попытку позже..";
                                \Yii::error("ORDER SAVE ERROR: " . var_export($order->getErrors(), true), 'debug');
                            } else {
                                $order->guid->generateGuid();
                                $redirect = $order->publicUrl;

                                //Перелинкуем товары из корзины в заказ
                                $basket->setAttributes([
                                    'fuser_id' => null,
                                    'order_id' => $order->id,
                                ]);
                                $basket->save();

                                //* Юзеры, фузеры *//

                                //Повторение сути из frontend/controllers/OrderController.php
                                if (UserHelper::isGuest()) {
                                    $fuser->user_id = null;
                                    $fuser->save();
                                }

                                //* /Юзеры, фузеры *//

                                //* КФСС Финал *//

                                /** @var Client $responseData */
                                $responseData = \Yii::$app->kfssApiV2->checkoutOrder($order);

                                if ($responseData && $responseData === true) {
                                    //Если запрос прошел нормально - выставляем статус что заказ пришел в удаленную систему
                                    $order->setStatus(ShopOrderStatus::STATUS_SUCCESS);
                                    $order->save();
                                    $success = true;
                                    $message = "Заказ №{$kfssOrderId} успешно создан.<br>Оператор свяжется с вами для подтверждения заказа.";
                                    $data['order'] = [
                                        'number' => $kfssOrderId,
                                        'price' => $orderKfss['sum'] ?? '',
                                        'discount' => max(0, $orderKfss['originalSum'] - $orderKfss['sum']),
                                        'delivery' => !empty($orderKfss['delivery']) ? max(0, $orderKfss['delivery']['price']) : '',
                                    ];
                                } else {
                                    $message = "Ошибка подтверждения заказа.";
                                    \Yii::error("Ошибка подтверждения заказа в КФСС (OneClick). Заказ №{$order->id} / КФСС №{$order->order_number}", 'checkoutLog');
                                    \Yii::error("Response №{$order->order_number}: " . var_export($responseData, true), 'checkoutLog');
                                }

                                //* /КФСС Финал *//
                            }
                        }

                        //Возвращаем нормальный заказ (или тот что был, например пустой)
                        $fuser->external_order_id = $kfssOrderIdNormal;
                        $fuser->save();

                        //Если в целом АПИ отключено то возвращаем в исходное состояние
                        if ($isKfssApiDisabled /*&& !empty($orderNumber)*/) {
                            \Yii::$app->kfssApiV2->isDisable = true;
                        }

                    } else {
                        $message = "Не удалось создать заказ. Попробуйте повторить попытку позже.";
                    }

                    //Если чтото пошло не так и успеха не получилось - удаляем добавленный в товар
                    if (!$success && $productAdd['success']) {
                        if (empty($basket)) {
                            $basket = ShopBasket::find()
                                ->where([
                                    'fuser_id' => $fuser->id,
                                    'product_id' => $productId,
                                    'type' => ShopBasket::TYPE_ONE_CLICK,
                                    'has_removed' => Common::BOOL_N_INT
                                ])
                                ->one();
                        }
                        if ($basket) {
                            $deleted = $basket->delete();
                        }
                    }

                    if ($needRedirect && !empty($redirect)) {
                        \Yii::$app->response->redirect($redirect);
                    }
                }//* Данные заказа *//
            }else{
                //Проблема с юзером, возможно заблокирован
                $success = false;
                $message = 'Возникла ошибка при создании заказа. Попробуйте повторить попытку позже.';
            }


        }else{
            $message = "Неверно указан номер телефона.";
        }

//        $user = \Yii::$app->user->identity;


        if ($debug) {
            var_dump('$userSaved');
            var_dump($userOk ?? 'EMPTY');
            var_dump('userId: ');
            var_dump($user->id ?? 'EMPTY');
            var_dump('fuserId: ');
            var_dump(\Yii::$app->shop->shopFuser->id);
            var_dump('shopBuyer');
            var_dump($buyer->id ?? 'EMPTY');
            var_dump('fPHONE');
            var_dump($fuser->phone ?? 'EMPTY');
            var_dump('uPHONE');
            var_dump($user->phone ?? 'EMPTY');
        }


        return [
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ];
    }

    /**
     * @param ShopFuser $shopFuser
     * @param bool $isNotify
     * @return static
     */
    static public function createOrder(\modules\shopandshow\models\shop\ShopFuser $shopFuser, $isNotify = true)
    {
        /** @var ShopOrder $order */
        $fuserBaskets = $shopFuser->shopBaskets;
        if (!empty($fuserBaskets)) {
            $order = static::createByFuser($shopFuser);

            //Номер заказа (KFSS ID)
            if (!empty($shopFuser->external_order_id)) {
                //К строке из-за ругани валидации
                $order->order_number = (string)$shopFuser->external_order_id;
            }

            //Данные по ПВЗ, сохраним если что то есть
            if ($shopFuser->pvz_data) {
                $order->pvz_data = $shopFuser->pvz_data;
            }

            //Стоимость доставки
            if ($order->order_number){
                $order->price_delivery = \Yii::$app->session->get("external_order_{$order->order_number}_delivery_price") ?? 0;
                \Yii::$app->session->remove("external_order_{$order->order_number}_delivery_price");
            }

            // источник заказа
            $order->source = self::SOURCE_SITE;

            //Источник детально может быть:
            //site - обычный заказ
            //mobile - обычный заказ с мобилы
            //fast_order - быстрый заказ с дектопа
            //fast_order_mobile - быстрый заказ с мобилы
            //one_click - заказ в 1 клик с десктопа
            //one_click_mobile - заказ в 1 клик с мобилы

            if (\Yii::$app->request->post('source_detail')) {
                $sourceDetail = \Yii::$app->request->post('source_detail');
            } else {
                $sourceDetail = \Yii::$app->mobileDetect->isMobile() ? self::SOURCE_DETAIL_MOBILE : self::SOURCE_DETAIL_SITE;
            }

            $order->source_detail = $sourceDetail;

            //Почему то значения по умолчанию через правила не срабатывают (
            //Приходится указывать явно
            $order->status_at = \Yii::$app->formatter->asTimestamp(time());

            if ($order->validate()) {

                if ($order->save()){
                    //Номер заказа (KFSS ID). Когда заказ сохранен - в fuser этот номер более не нужен
                    if (!empty($shopFuser->external_order_id)) {
                        $shopFuser->external_order_id = null;
                        $shopFuser->save();
                    }

                    foreach ($fuserBaskets as $basket) {

                        $basket->unlink('fuser', $shopFuser);
                        $basket->link('order', $order);

                        /**
                         * @var $basket ShopBasket
                         */
                        if ($basket->hasRemoved()) {
                        } else {
                        }
                    }

                    if ($shopFuser->discountCoupons) {
                        foreach ($shopFuser->discountCoupons as $discountCoupon) {
                            $shopOrder2discountCoupon = new ShopOrder2discountCoupon();
                            $shopOrder2discountCoupon->order_id = $order->id;
                            $shopOrder2discountCoupon->discount_coupon_id = $discountCoupon->id;

                            if (!$shopOrder2discountCoupon->save()) {
                                print_r($shopOrder2discountCoupon->errors);
                                die;
                            }
                        }

                        $shopFuser->discount_coupons = [];
                        $shopFuser->save(false);
                    }

                    if ($shopFuser->ssShopFuserDiscount) {
                        $shopFuser->ssShopFuserDiscount->link('shopOrder', $order);
                        $shopFuser->ssShopFuserDiscount->unlink('shopFuser', $shopFuser);
                    }

                    //Notify admins
                    if (false && \Yii::$app->shop->notifyEmails && $isNotify) {
                        foreach (\Yii::$app->shop->notifyEmails as $email) {

                            \Yii::$app->mailer->htmlLayout = false;
                            \Yii::$app->mailer->textLayout = false;

                            \Yii::$app->mailer->compose('modules/shop/admin_create_order', [
                                'order' => $order
                            ])
                                ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                                ->setTo($email)
                                ->setSubject('Заказ на новом сайте!' . ' №' . $order->id)
                                ->send();
                        }
                    }

                    // обновляем статус ss_shares_selling пользовательской корзины
//                SsShareSeller::setStatusOrderByFuser($shopFuser, $order);

                    //*  *//
                }else{
                    \Yii::error("Can't save order 6. Errors: " . var_export($order->getErrors(), true), 'debug');
                    return false;
                }

            } else {
//                Developers::reportProblem('Ошибка в заказе 4');
                \Yii::error("Can't validate order 4. Errors: " . var_export($order->getErrors(), true), 'debug');
                return false;
            }

            return $order;
        } else {
            \Yii::error('У fuser #' . $shopFuser->id . 'нет привязанных корзин', 'checkoutLog');
        }

        return false;

    }


    /**
     * @param ShopFuser $shopFuser
     * @param bool $isNotify
     * @return static
     */
    static public function createOrderOneClick($shopFuser, $isNotify = true)
    {
        /** @var ShopOrder $order */
        $fuserBaskets = $shopFuser->shopBasketsOneClick;
        if (!empty($fuserBaskets)) {

            $order = static::createByFuser($shopFuser);
            $order->price = $shopFuser->getMoney(ShopBasket::TYPE_ONE_CLICK)->getAmount() / $shopFuser->getMoney(ShopBasket::TYPE_ONE_CLICK)->getCurrency()->getSubUnit();

            //Номер заказа (KFSS ID)
            if (!empty($shopFuser->external_order_id)) {
                $order->order_number = $shopFuser->external_order_id;
            }

            // источник заказа
            $order->source = self::SOURCE_SITE;
            $order->source_detail = self::SOURCE_DETAIL_ONE_CLICK;

            if ($order->save()) {
                //Номер заказа (KFSS ID). Когда заказ сохранен - в fuser этот номер более не нужен
                if (!empty($shopFuser->external_order_id)) {
                    $shopFuser->external_order_id = null;
                    $shopFuser->save();
                }

                foreach ($fuserBaskets as $basket) {

                    $basket->unlink('fuser', $shopFuser);
                    $basket->link('order', $order);

                    /**
                     * @var $basket ShopBasket
                     */
                    if ($basket->hasRemoved()) {
                    } else {
                    }
                }

                if ($shopFuser->discountCoupons) {
                    foreach ($shopFuser->discountCoupons as $discountCoupon) {
                        $shopOrder2discountCoupon = new ShopOrder2discountCoupon();
                        $shopOrder2discountCoupon->order_id = $order->id;
                        $shopOrder2discountCoupon->discount_coupon_id = $discountCoupon->id;

                        if (!$shopOrder2discountCoupon->save()) {
                            print_r($shopOrder2discountCoupon->errors);
                            die;
                        }
                    }

                    $shopFuser->discount_coupons = [];
                    $shopFuser->save(false);
                }

                if ($shopFuser->ssShopFuserDiscount) {
                    $shopFuser->ssShopFuserDiscount->link('shopOrder', $order);
                    $shopFuser->ssShopFuserDiscount->unlink('shopFuser', $shopFuser);
                }

                //Notify admins
                if (\Yii::$app->shop->notifyEmails && $isNotify && false) {
                    foreach (\Yii::$app->shop->notifyEmails as $email) {

                        \Yii::$app->mailer->htmlLayout = false;
                        \Yii::$app->mailer->textLayout = false;

                        \Yii::$app->mailer->compose('modules/shop/admin_create_order', [
                            'order' => $order
                        ])
                            ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                            ->setTo($email)
                            ->setSubject('Заказ на новом сайте!' . ' №' . $order->id)
                            ->send();
                    }
                }

                // обновляем статус ss_shares_selling пользовательской корзины
//            SsShareSeller::setStatusOrderByFuser($shopFuser, $order);
            } else {
                Developers::reportProblem('Ошибка в заказе 1 клика 1');
            }

            return $order;
        } else {
            \Yii::error('У fuser #' . $shopFuser->id . 'нет привязанных корзин', 'checkoutLog');
        }

        return false;
    }

    /**
     * @param ShopFuser $shopFuser
     * @return static
     */
    static public function createByFuser($shopFuser)
    {
        $orderData = Api::getCartBillingData();

        $order = new static();

        $order->site_id         = $shopFuser->site->id;
        $order->person_type_id  = $shopFuser->person_type_id;
        $order->buyer_id        = $shopFuser->buyer_id;
        $order->user_id         = $shopFuser->user_id;

//        $order->price           = $shopFuser->money->getAmount() / $shopFuser->money->getCurrency()->getSubUnit();
        $order->price           = $orderData['total'];
        $order->currency_code   = 'RUB';
        $order->pay_system_id   = $shopFuser->paySystem->id;
        $order->tax_value       = 0; //$shopFuser->moneyVat->getValue();
        $order->discount_value  = 0; //$shopFuser->moneyDiscount->getValue();
        $order->delivery_id     = $shopFuser->delivery_id;
        $order->store_id        = $shopFuser->store_id;

        //Так не работает. Устанавливается в другом месте
        if ($shopFuser->delivery)
        {
            $order->price_delivery  = $shopFuser->delivery->money->getAmount() / $shopFuser->delivery->money->getCurrency()->getSubUnit();
        }

        return $order;
    }

    //Завершение оформления заказа, данные по пользователю
    public static function getCheckoutUser()
    {
        $response = [
            'success' => true,
            'message' => '',
            'data' => [],
        ];

        $fuser = \Yii::$app->shop->shopFuser;
        $user = \common\helpers\User::getUser();
        $userData = Api::getUserData(); //Фиговый подход, переделать по нормальному

        \Yii::error("fuserId = " . $fuser->id, 'debug');

        if (!$user){
            //Гость, ищем по телефону
            $userNew = \common\models\User::findByPhone($fuser->phone);

            //Если не нашелся по телефону - пробуем зарегистрировать
            if (!$userNew) {
                $userNew = new QuickOrder();

                $userAttributes = [
                    'phone' => $userData['phoneNumber'] ?? '',
                    'email' => $userData['email'] ?? '',
                    'name' => $userData['firstName'] ?? '',
                    'patronymic' => $userData['middleName'] ?? '',
                    'surname' => $userData['lastName'] ?? '',
                ];

                $userNew->setAttributes($userAttributes);
                //TODO Добавить валидацию
                if (!$user = $userNew->signup()){
                    $response['success'] = false;
                    $response['message'] = 'Ошибка при сохранении пользователя';
                }else{
                    \Yii::error("Checkout. User is GUEST, but successfully created. userId = " . $user->id, 'debug');
                }
            }else{
                //В качестве быстрого варианта используем подход в забывании старого фузера (а точнее снимаем у него связь с текущим пользователем)
                //и использовании текущего для данного пользователя
                $user = $userNew;
                $affected = ShopFuser::updateAll(['user_id' => null], ['user_id' => $user->id]);

                \Yii::error("Checkout. User is GUEST, but found by phone. userId = " . $user->id, 'debug');
            }
        }

        //Пользователь или уже был авторизован или успешно создался новый
        if ($user) {
            //Связываем с фузером
            $fuser->user_id = $user->id;
            if (!$fuser->save()){
                \Yii::error("Err save fuser. Errs: " . var_export($fuser->getErrors(), true), 'debug');
            }
        }else{
            //Без пользователя продолжать смысла нет
            return $response;
        }

        //Тут уже даже для гостя должен появиться юзер
        if ($user) {

            //* BUYER *//

            //Архитектура обязывает иметь в наличии байера
            $buyer = $fuser->buyer;

            if (!$buyer) {
                \Yii::error("Fuser have no buyer", 'debug');
                //По свящи из фузера баер не нашелся, пробуем поискать по пользователю
                $buyer = ShopBuyer::find()->where(['cms_user_id' => $user->id])->one();

                if (!$buyer){
                    \Yii::error("Buyer not found for user {$user->id}", 'debug');
                    $buyer = new ShopBuyer([
                        'shop_person_type_id' => 1, //1 - физ лицо. Из-за обновления моделей не используем констарнты из старых
                        'cms_user_id' => $user->id
                    ]);
                    if (!$buyer->save()){
                        \Yii::error("Error save new buyer. Errs: " . var_export($buyer->getErrors(), true), 'debug');
                    }
                }else{
                    \Yii::error("Buyer found for user {$user->id} //{$buyer->id}", 'debug');
                }

                if (!empty($buyer->id)) {
                    \Yii::error("Save buyer {$buyer->id} for fuser {$fuser->id}", 'debug');
                    $fuser->buyer_id = $buyer->id;
                    $fuser->save();
                }else{
                    //ERR
                    $response['success'] = false;
                    $response['message'] = 'Ошибка при сохранении данных пользователя';
                    return $response;
                }

                //* BUYER *//
            }

            //Подписка
            //В новых моделях не связанных свойств, так что пока все еще используем старую модель
            $userModelOld = \common\models\user\User::findOne($user->id);
//            if ($user instanceof \common\models\user\User) {
            if ($userModelOld) {
                $userModelOld->relatedPropertiesModel->setAttribute('SUBSCRIBE_TO_NEWSLETTER', (string)\Yii::$app->request->post('is_subscribe', false));
                $userModelOld->relatedPropertiesModel->setAttribute('LAST_NAME', $userData['lastName']);
                $userModelOld->relatedPropertiesModel->setAttribute('PATRONYMIC', $userData['middleName']);
                $userModelOld->relatedPropertiesModel->save();
                $userModelOld->save();
            }

            $response['data'] = $user;
        }else{
            $response['success'] = false;
        }

        return $response;
    }

    public static function isOnlinePaymentAllowed(){
        return (Setting::isOnlinePaymentAllowed() || \Yii::$app->session->get('op')) ? true : false;
    }
}