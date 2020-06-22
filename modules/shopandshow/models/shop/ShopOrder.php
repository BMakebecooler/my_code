<?php

namespace modules\shopandshow\models\shop;

use common\helpers\ArrayHelper;
use common\helpers\Developers;
use common\helpers\User as UserHelper;
use common\models\user\User;
use Exception;
use modules\shopandshow\behaviors\TimestampBehavior as SsTimestampBehavior;
use modules\shopandshow\models\common\GuidBehavior;
use modules\shopandshow\models\monitoringday\PlanHour;
use modules\shopandshow\models\shares\SsShareSeller;
use modules\shopandshow\services\Survey;
use skeeks\cms\components\Cms;
use skeeks\cms\shop\models\ShopOrder as SxShopOrder;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\httpclient\Client;

/**
 * Class ShopOrder
 *
 * @property integer $counter_send_queue
 * @property integer $counter_error_queue
 * @property datetime $last_send_queue_at
 * @property ShopOrderStatus $status
 * @property string $guid_id
 * @property string $source
 * @property string $source_detail
 * @property string $order_number
 * @property User $user
 * @property GuidBehavior $guid
 * @property string $pvz_data
 * @property string $order_payment_number
 * @property int $do_not_need_confirm_call
 * @property int $count_payment
 */
class ShopOrder extends SxShopOrder
{

    const SOURCE_UNKNOWN = 'Неизвестно';
    const SOURCE_ALL = 'all';
    const SOURCE_SITE = 'site';
    const SOURCE_SITE_KFSS = 'site_kfss';
    const SOURCE_BITRIX = 'bitrix';
    const SOURCE_KFSS = 'kfss';
    const SOURCE_MOBILE_APP = 'mobile_app';
    const SOURCE_CPA = 'cpa';

    const SOURCE_DETAIL_UNKNOWN = 'Неизвестно';
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
            self::SOURCE_SITE => 'Сайт',
            self::SOURCE_SITE_KFSS => 'Сайт (КФСС)',
            self::SOURCE_KFSS => 'КФСС',
            self::SOURCE_BITRIX => 'Битрикс',
            self::SOURCE_MOBILE_APP => 'Моб.приложение',
        ],
        'detail' => [
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

    public function behaviors()
    {
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
     * @param $checkoutPhone - доп телефон для баера
     * @param $type - basket type
     * @return bool|ShopOrder|string
     * @return int $type
     * @throws Exception
     */
    static public function checkout($checkoutPhone = null, $type = ShopBasket::TYPE_DEFAULT)
    {

        /**
         * @var $shopFuser ShopFuser
         */
        $shopFuser = \Yii::$app->shop->shopFuser;
        $shopFuser->loadDefaultValues();

        //Создание покупателя в зависимости от выбранного типа
        //[DEPRECATED Исправлено на универсальный вариант] Проверяет авторизованного пользователя!
        $shopBuyer = $shopFuser->createModelShopBuyer();

        if ($shopFuser->validate() && $shopBuyer->validate()) {

            //Сохранение покупателя
            if ($shopBuyer->isNewRecord) {
                if ($buyerName = UserHelper::getUser()->displayName) {
                    $shopBuyer->name = $buyerName;
                }

                if (!$shopBuyer->save()) {

                    \Yii::error('Ошибка в заказе 2 - Not save buyer' . var_export($shopBuyer->getErrors()));

                    throw new Exception('Not save buyer');
                }
            }

            /**
             * Сохраняем доп телефон
             */
            if ($checkoutPhone) {
                $shopBuyer->relatedPropertiesModel->setAttribute('phone', $checkoutPhone);
                $shopBuyer->relatedPropertiesModel->save(false);
            }

            //Текущий профиль покупателя присваивается текущей корзине
            $shopFuser->buyer_id = $shopBuyer->id;

            try {
                if ($shopFuser->user_id){
                    $user = \common\models\User::findOne(['id' => $shopFuser->user_id]);
                }elseif ($shopFuser->phone){
//                    $user = User::findByPhone($shopFuser->phone);
                    $user = \common\models\User::findByPhone($shopFuser->phone);
                }elseif (\Yii::$app->user->identity){
                    $user = \Yii::$app->user->identity;
                }

                //TODO Делать это только если в фузере еще нет указания на юзера и юзер авторизован
//                if ($user = \Yii::$app->user->identity) {
                if ($user) {
                    $shopFuser->user_id = $user->id;
                    $shopFuser->save();

                    $shopBuyer->cms_user_id = $user->id;
                    $shopBuyer->save();
                }

                /**
                 * Сохраняем телефон указанный при оформлении заказа
                 */
                if ($checkoutPhone) {
                    if (!$user->phone){
                        $user->phone = \common\helpers\Strings::getPhoneClean($checkoutPhone);
                        $user->phone_is_approved = YES_INT;

                        if (!$user->save(false, ['phone', 'phone_is_approved'])) {

                            Developers::reportProblem('Ошибка в заказе 3' . var_export($user->getErrors()));
                            \Yii::error('Ошибка в заказе 3' . var_export($user->getErrors()));

//                        throw new Exception('Not save user');
                        }
                    }
                }


                if ($type == ShopBasket::TYPE_DEFAULT) {
                    $newOrder = self::createOrder($shopFuser);
                } elseif ($type == ShopBasket::TYPE_ONE_CLICK) {
                    $newOrder = self::createOrderOneClick($shopFuser);
                } else {
                    throw new Exception('Не известный тип заказа!');
                }
//                \Yii::error('Order_number: ' . $newOrder->order_number . ' Order_guid: '. $newOrder->guid_id, 'checkoutLog');
                \Yii::info('Order_number: ' . $newOrder->order_number . ' Order_guid: ' . $newOrder->guid_id, 'checkoutLog');
                if ($newOrder) {

                    if (SS_SITE == CONST_SITE_SHIK) {

                        /**
                         * Отправляем в очередь новый заказ
                         */
                        \Yii::$app->shopAndShow->sendCreateOrder($newOrder);
                    }

                    //Оплачиваемые заказы через КЦ не гоняем
                    if ($newOrder->pay_system_id == \Yii::$app->kfssApiV2::PAY_SYSTEM_ID_CARD && \Yii::$app->kfssApiV2->isOnlinePaymentAllowed()) {
                        //TODO Написать Текс СМС для заказов без подтверждения КЦ
                        $text = 'Ваш заказ принят. Ожидайте дальнейших уведомлений по статусу Вашего заказа.';
                        \Yii::info('Send sms for skill user', 'checkoutLog');
                    } else {
                        $text = 'Ваш заказ принят. Наши операторы свяжутся с Вами для уточнения деталей заказа.';
                    }

                    \common\helpers\Order::sendSmsCreateOrder($newOrder, $text);

                    $userEmail = Survey::getUserEmail($newOrder->user);

                    //Отправляем письмо если понятно куда и если есть номер заказа
                    if ($userEmail && $newOrder->order_number){
                        \common\helpers\Order::sendEmailCreateOrder(
                            $newOrder,
                            "Вы оформили заказ №{$newOrder->order_number} на Shop&Show.",
                            'modules/shop/client_new_order'
                        );
                    }

                    //Опросник
                    Survey::sendSurvey(Survey::ORDER_FINISH_TYPE, $newOrder);

                    //Оплачиваемые заказы в КФСС не отправляем на данном этапе
                    //ОТКЛЮЧЕНО ДЛЯ ТЕСТОВ С ЭМУЛЯЦИЕЙ ОПЛАТЫ! TODO ВЕРНУТЬ!
//                    if (false && $newOrder->pay_system_id == \Yii::$app->kfssApiV2::PAY_SYSTEM_ID_CARD){ //Для теста
                    if ($newOrder->pay_system_id == \Yii::$app->kfssApiV2::PAY_SYSTEM_ID_CARD && \Yii::$app->kfssApiV2->isOnlinePaymentAllowed()){ //Для боя
                        $newOrder->do_not_need_confirm_call = 1;
                        $newOrder->save();

                        //инициируем и переводим на оплату

                        //[DEPRECATED] Сбер
                        if (false) {
                            if (\Yii::$app->kfssApiV2->isOnlinePaymentAllowed()) { //Оплата только для избранных //TODO Убрать при переходе в полный боевой режим
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
                            if (\common\models\ShopOrder::isOnlinePaymentAllowed()) { //Оплата только для избранных  или по факту включения такой возможности для всех
                                $payFormUrl = \Yii::$app->kfssAlfaApiV1->registerOrderPayment($newOrder);
                                if ($payFormUrl) {
                                    //Так как вызывается в аяксе и надо как то вернуть путь к форме - передаем как коммент. Так себе варик.
                                    $newOrder->comments = $payFormUrl;

                                    if (!$newOrder->save()){
                                        \Yii::error(var_export($newOrder->getErrors(), true), 'checkoutLog');
                                    }else{
                                        //Оплата зарегистрирована, оформляем заказ
                                        /** @var Client $responseData */
                                        $responseData = \Yii::$app->kfssApiV2->checkoutOrder($newOrder);

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
                            if ($newOrder->pay_system_id == \Yii::$app->kfssApiV2::PAY_SYSTEM_ID_CARD && \Yii::$app->kfssApiV2->isOnlinePaymentAllowed()) {
                                //orderPayId = 'ff86094c-66ea-7055-9070-dc9504b272e3'
                                $orderPayId = 'ff86094c-66ea-7055-9070-' . (substr(md5(time()), 0, 12));
                                \Yii::$app->kfssApiV2->payOrder($newOrder->order_number, $orderPayId);
                            }
                        }

                        // /ДЛЯ ТЕСТОВ, ЭМУЛЯЦИЯ ОПЛАТЫ! УБРАТЬ ПОСЛЕ ТЕСТОВ!!!

                        /** @var Client $responseData */
                        $responseData = \Yii::$app->kfssApiV2->checkoutOrder($newOrder);

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
                    \Yii::error('Не получилось создать заказ 4' . print_r($newOrder->getErrors(), true), 'checkoutLog');
                }

                return $newOrder;

            } catch (\Exception $e) {

                Developers::reportProblem('Ошибка в заказе 3' . PHP_EOL . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
                \Yii::error('Ошибка в заказе 3' . PHP_EOL . $e->getMessage() . PHP_EOL . $e->getTraceAsString(), 'checkoutLog');
                return false;
            }
        }
        \Yii::error('Не получилось создать заказ 5. 1)' . print_r($shopFuser->getErrors(), true) . ' | 2)' . print_r($shopBuyer->getErrors(), 'checkoutLog'));

        return false;
    }

    /**
     * @param ShopFuser $shopFuser
     * @param bool $isNotify
     * @return static
     */
    static public function createOrder(ShopFuser $shopFuser, $isNotify = true)
    {
        /** @var ShopOrder $order */
        $fuserBasckets = $shopFuser->shopBaskets;
        if (!empty($fuserBasckets)) {
            $order = static::createByFuser($shopFuser);

            //Номер заказа (KFSS ID)
            if (!empty($shopFuser->external_order_id)) {
                $order->order_number = $shopFuser->external_order_id;
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

            if ($order->save()) {
                //Номер заказа (KFSS ID). Когда заказ сохранен - в fuser этот номер более не нужен
                if (!empty($shopFuser->external_order_id)) {
                    $shopFuser->external_order_id = null;
                    $shopFuser->save();
                }

                foreach ($fuserBasckets as $basket) {

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
                SsShareSeller::setStatusOrderByFuser($shopFuser, $order);

                //*  *//

            } else {
                Developers::reportProblem('Ошибка в заказе 4');
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
    static public function createOrderOneClick(ShopFuser $shopFuser, $isNotify = true)
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
     *
     * @param $status
     */
    public function setStatus($status)
    {
        $this->status_code = $status;
    }

    /**
     * Увеличить счетчик отправленных попыток в очередь
     */
    public function incrSendCounter()
    {
        $this->counter_send_queue += 1;
    }

    /**
     * Увеличить счетчик полученных ошибок
     */
    public function incrSendErrorCounter()
    {
        $this->counter_error_queue += 1;
    }

    /**
     * Обновить дату последнего отправления в очередь
     */
    public function updateLastSendQueueAt()
    {
        $this->last_send_queue_at = date('Y-m-d H:i:s');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(ShopOrderStatus::className(), ['code' => 'status_code']);
    }

    /**
     * Вернуть только активные товары в корзине
     * @return \yii\db\ActiveQuery
     */
    public function getShopBaskets()
    {
        return parent::getShopBaskets()
            ->andWhere(['has_removed' => ShopBasket::HAS_REMOVED_FALSE]);
    }

    /**
     * Получить телефон у заказа
     * @return string
     */
    public function getPhone()
    {
        $desiredPhone = ($this->buyer && $this->buyer->relatedPropertiesModel) ? $this->buyer->relatedPropertiesModel->getAttribute('phone') : false;
        $phone = $desiredPhone ?: (\Yii::$app->user->identity ? \Yii::$app->user->identity->phone : '');

        return $phone;
    }

    /**
     * Получить номер заказа
     * @return int
     */
    public function getNumber()
    {
        return $this->order_number ?: $this->id;
    }

    public static function getSourceLabel($source)
    {
        return isset(self::$sourceLabels['main'][$source]) ? self::$sourceLabels['main'][$source] : ($source ?: 'НЕ ОПРЕДЕЛЕНО');
    }

    public static function getSourceDetailLabel($source)
    {
        return isset(self::$sourceLabels['detail'][$source]) ? self::$sourceLabels['detail'][$source] : ($source ?: 'НЕ ОПРЕДЕЛЕНО');
    }

    public function afterInstertCallback($e)
    {
        //Если при добавлении заказа его статус_код не является начальным (например при импорте из удаленной системы)
        //то необходимо вставить в историю изменения (создания) сначала этот самый изначальный статус
        //а затем уже записать изменение на тот что пришел

        (new ShopOrderChange([
            'type' => ShopOrderChange::ORDER_ADDED,
            'shop_order_id' => $this->id,
            'status_code' => ShopOrderStatus::STATUS_WAIT_PAY
        ]))->save();

        if ($this->status->code != ShopOrderStatus::STATUS_WAIT_PAY) {
            (new ShopOrderChange([
                'type' => ShopOrderChange::ORDER_STATUS_CHANGED,
                'shop_order_id' => $this->id,
                'data' => [
                    'status' => $this->status->name
                ],
                'status_code' => $this->status->code
            ]))->save();
        }
    }

    public function beforeUpdateCallback($e)
    {

        if ($this->isAttributeChanged('canceled')) {
            $this->canceled_at = \Yii::$app->formatter->asTimestamp(time());
        }

        if ($this->isAttributeChanged('payed')) {
            $this->payed_at = \Yii::$app->formatter->asTimestamp(time());
        }

        if ($this->isAttributeChanged('status_code')) {
            $this->status_at = \Yii::$app->formatter->asTimestamp(time());

            (new ShopOrderChange([
                'type' => ShopOrderChange::ORDER_STATUS_CHANGED,
                'shop_order_id' => $this->id,
                'data' =>
                    [
                        'status' => $this->status->name
                    ],
                'status_code' => $this->status->code
            ]))->save();


            //Не работает да и отправляем другими механизмами
            /*
            //Письмо тому кто заказывает
            if ($this->user->email)
            {
                try
                {
                    \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

                    \Yii::$app->mailer->compose('order-status-change', [
                        'order'  => $this
                    ])
                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                        ->setTo($this->user->email)
                        ->setSubject(\Yii::$app->cms->appName . ': ' .\Yii::t('skeeks/shop/app','Change order status'). ' #' . $this->id)
                        ->send();

                } catch (\Exception $e)
                {
                    \Yii::error('Ошибка отправки email: ' . $e->getMessage(), Module::className());
                }

            }
            */
        }

        if ($this->isAttributeChanged('allow_payment') && $this->allow_payment == Cms::BOOL_Y) {
            (new ShopOrderChange([
                'type' => ShopOrderChange::ORDER_ALLOW_PAYMENT,
                'shop_order_id' => $this->id,
            ]))->save();

            //Не работает да и отправляем другими механизмами
            /*
            //Письмо тому кто заказывает
            if ($this->user->email)
            {
                try
                {

                    \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

                    \Yii::$app->mailer->compose('order-allow-payment', [
                        'order'  => $this
                    ])
                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                        ->setTo($this->user->email)
                        ->setSubject(\Yii::$app->cms->appName . ': ' .\Yii::t('skeeks/shop/app','Resolution of payment on request'). ' #' . $this->id)
                        ->send();

                } catch (\Exception $e)
                {
                    \Yii::error('Ошибка отправки email: ' . $e->getMessage(), Module::className());
                }
            }
            */
        }

        if ($this->isAttributeChanged('allow_delivery') && $this->allow_delivery == Cms::BOOL_Y) {
            (new ShopOrderChange([
                'type' => ShopOrderChange::ORDER_ALLOW_DELIVERY,
                'shop_order_id' => $this->id,
            ]))->save();

            //Не работает да и отправляем другими механизмами
            /*
            //Письмо тому кто заказывает
            if ($this->user->email)
            {
                try
                {

                    \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

                    \Yii::$app->mailer->compose('order-allow-delivery', [
                        'order'  => $this
                    ])
                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                        ->setTo($this->user->email)
                        ->setSubject(\Yii::$app->cms->appName . ': ' .\Yii::t('skeeks/shop/app','Resolution of payment on request'). ' #' . $this->id)
                        ->send();

                } catch (\Exception $e)
                {
                    \Yii::error('Ошибка отправки email: ' . $e->getMessage(), Module::className());
                }
            }
            */
        }

        if ($this->isAttributeChanged('canceled') && $this->canceled == Cms::BOOL_Y) {
            (new ShopOrderChange([
                'type' => ShopOrderChange::ORDER_CANCELED,
                'shop_order_id' => $this->id,
                'data' => [
                    'reason_canceled' => $this->reason_canceled
                ],
                'status_code' => ShopOrderStatus::STATUS_CANCELED
            ]))->save();

            //Не работает да и отправляем другими механизмами
            /*
            //Письмо тому кто заказывает
            if ($this->user->email)
            {
                try
                {

                    \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

                    \Yii::$app->mailer->compose('order-canceled', [
                        'order'  => $this
                    ])
                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                        ->setTo($this->user->email)
                        ->setSubject(\Yii::$app->cms->appName . ': ' .\Yii::t('skeeks/shop/app','Cancellations'). ' #' . $this->id)
                        ->send();
                } catch (\Exception $e)
                {
                    \Yii::error('Ошибка отправки email: ' . $e->getMessage(), Module::className());
                }
            }
            */
        }

    }

    public function getIsSourceCpa()
    {
        return $this->source == self::SOURCE_CPA;
    }


    public static function getCurrentFact()
    {
        return self::find()
            ->select('sum(price)')
            ->andWhere('created_at >= UNIX_TIMESTAMP() - 10 and source = \'site\'')
            ->scalar();
    }

    public static function getCurrentCount()
    {
        return self::find()
            ->select('count(id)')
            ->andWhere('created_at >= UNIX_TIMESTAMP() - 10 and source = \'site\'')
            ->scalar();
    }


    public static function getCurrentFactGraph()
    {
        $datetime = new \DateTime();
        $datetime->setTime(00, 00, 00);
        return self::find()
            ->select('sum(price)')
            ->andWhere(['source' => self::SOURCE_SITE])
            ->andWhere(['>=', 'created_at', $datetime->getTimestamp()])
            ->scalar();
    }

    public static function getCurrentCountGraph()
    {
        $datetime = new \DateTime();
        $datetime->setTime(00, 00, 00);
        return self::find()
            ->select('count(id)')
            ->andWhere(['source' => self::SOURCE_SITE])
            ->andWhere(['>=', 'created_at', $datetime->getTimestamp()])
            ->scalar();
    }

    public static function getCurrentPlan()
    {
        return PlanHour::find()
            ->select(new Expression('sum_plan / 60 / 60'))
            ->innerJoin('ss_monitoring_plan_day', 'ss_monitoring_plan_hour.plan_id = ss_monitoring_plan_day.id')
            ->where('date = curdate() and type_plan = \'site\' and hour = hour(curtime())')
            ->scalar();
    }
    public static function getCurrentPlanGraph()
    {
        return PlanHour::find()
            ->select(new Expression('sum(sum_plan*percent/100) as plan'))
            ->innerJoin('ss_monitoring_plan_day', 'ss_monitoring_plan_hour.plan_id = ss_monitoring_plan_day.id')
            ->where(' type_plan = \'site\' and hour <= hour(curtime())')
            ->andWhere(['>=','date', date('Y-m-d')])
            ->scalar();
    }


}