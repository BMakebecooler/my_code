<?php

namespace modules\shopandshow\models\newEntities\shop;

use common\helpers\Strings;
use common\models\user\User;
use console\controllers\queues\jobs\Job;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\shop\ShopBuyer;
use modules\shopandshow\models\shop\ShopFuser;
use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopOrderStatus;
use modules\shopandshow\models\users\UserEmail;

class Order extends ShopOrder
{
    public $info_source;

    public $order_guid;
    public $order_number;
    public $order_createdate;
    public $order_comment;
    public $order_price;
    public $order_original_price;
    public $order_source;
    public $order_source_detail;

    public $order_delivery = [];
    public $order_discount = [];

    public $client_guid;
    public $client_name;
    public $client_email;
    public $client_bitrix_id;
    public $client_phone;
    public $client_ext_phone;

    public $client_address = [];

    // работаем пока только с физ лицами. Когда появятся юр.лица, в json должен будет добавиться для этого отдельный атрибут
    const PERSON_TYPE_ID = 1;

    public function addData()
    {
        if (!$this->order_guid) {
            Job::dump('guid empty');

            return false;
        }

        /** @var ShopOrder $shopOrder */
        if ($shopOrder = Guids::getEntityByGuid($this->order_guid)) {
            // заказ найден, возможно потом потребуется логика по его обновлению

            $this->updateOrder($shopOrder);

            return true;
        }

        // создаем заказ
        return $this->createNewOrder();
    }


    /**
     * Обновление заказа
     * @return bool
     */
    protected function updateOrder($order)
    {
        $sendMsg = false;
        $order->updated_at = time();

        $order->price = $this->order_price;
        $order->price_delivery = $this->order_delivery['Price'] ?? 0;
        $order->discount_value = $this->order_original_price - $this->order_price;

        if (empty($order->order_number)) {
            $order->order_number = $this->order_number;
            $sendMsg = true;
        }

        if (!$order->save()) {
            Job::dump(print_r($order->getErrors(), true));
            return false;
        }


        if ($sendMsg) {
            if ($order->do_not_need_confirm_call) {
                $template = 'modules/shop/expert_client_new_order';
                $subject = \Yii::$app->cms->appName
                    . ': '
                    . \Yii::t('skeeks/shop/app', 'New order')
                    . ' №' . $this->order_number
                    . '. Ваш заказ будет отправлен '
                    . \Yii::$app->formatter->asDatetime($this->updated_at + 86400, 'php:j F');
            } else {
                $template = 'modules/shop/client_new_order';
                $subject = \Yii::$app->cms->appName . ': ' . \Yii::t('skeeks/shop/app', 'New order');
            }

            \common\helpers\Order::sendEmailCreateOrder(
                $order,
                $subject,
                $template
            );

            if ($order->do_not_need_confirm_call) {
                if ($order->order_number) {
                    //TODO Написать Текс СМС для заказов без подтверждения КЦ
                    $text = 'Номер вашего заказа: ' . $order->order_number;
                    \Yii::info('Send sms for skill user', 'checkoutLog');
                }
            } else {
                if ($order->order_number) {
                    $text = 'Номер вашего заказа: ' . $order->order_number;
                }
            }

            if (isset($text)) {
                \common\helpers\Order::sendSmsCreateOrder($order, $text);
            }
        }

        return true;
    }

    /**
     * Создание нового заказа
     * @return bool
     */
    protected function createNewOrder()
    {
        /** @var User $user */
        $user = $this->getOrCreateUser();
        if (!$user) {
            Job::dump('failed to getOrCreateUser');
            return false;
        }

        $shopBuyer = ShopBuyer::findOne(['cms_user_id' => $user->id]);
        if (!$shopBuyer) {
            $shopBuyer = new ShopBuyer([
                'shop_person_type_id' => self::PERSON_TYPE_ID,
                'cms_user_id' => $user->id
            ]);

            if (!$shopBuyer->save()) {
                Job::dump(print_r($shopBuyer->getErrors(), true));
                return false;
            }
        }

        $shopFuser = new ShopFuser([
            'person_type_id' => self::PERSON_TYPE_ID,
            'buyer_id' => $shopBuyer->id,
            'user_id' => $user->id
        ]);
        $shopFuser->loadDefaultValues();

        try {
            $order = ShopOrder::createByFuser($shopFuser);
            $order->guid->setGuid($this->order_guid);
        } catch (\Exception $e) {
            Job::dump('Ошибка при создании заказа ' . $e->getMessage());
            return false;
        }

        $order->source = $this->order_source;
        $order->source_detail = $this->order_source_detail;

        $order->setStatus(ShopOrderStatus::STATUS_SUCCESS);
        $order->created_by = $user->id;

        $orderDate = strtotime($this->order_createdate);

        $order->created_at = $orderDate;
        $order->updated_at = $orderDate;
        $order->status_at = $orderDate;

        $order->price = $this->order_price;
        $order->price_delivery = $this->order_delivery['Price'] ?? 0;
        $order->discount_value = $this->order_original_price - $this->order_price;

        $order->order_number = $this->order_number;

        if (!$order->save()) {
            Job::dump(print_r($order->getErrors(), true));
            return false;
        }

        return true;
    }

    /**
     * Создает пользователя
     * @return User | bool
     */
    protected function getOrCreateUser()
    {
        /** @var User $user */
        $user = null;

        if (!$this->client_guid) {
            Job::dump('empty client guid');
            return false;
        }

        Job::dump("searching user by guid [{$this->client_guid}]: ");
        $user = Guids::getEntityByGuid($this->client_guid);
        if ($user) {
            Job::dump("{$user->displayName} [{$user->id}]");

            return $user;
        } else {
            Job::dump("user not found");
        }

        if (!\Yii::$app->has('user', false)) {
            \Yii::$app->set('user', [
                'class' => 'yii\web\User',
                'identityClass' => 'common\models\user\User'
            ]);
        }

        $model = new \common\models\user\authorizations\SignupForm();
        $model->setScenario(User::SCENARIO_RIGISTRATION_FROM_BITRIX);

        //Форматирование более не актуально
        if (false) {
            // ожидаем +0 000 000-00-00
            if (!preg_match('/^\+\d\s\d{3}\s\d{3}\-\d{2}\-\d{2}$/', trim($this->client_phone))) {
                // режем все кроме цифр
                $this->client_phone = preg_replace('/[^0-9]/', '', $this->client_phone);
                // разбиваем на токены
                if (preg_match('/^(\d)(\d{3})(\d{3})(\d{2})(\d{2})$/', $this->client_phone, $match)) {
                    // собираем по формату
                    $this->client_phone = sprintf('+%d %03d %03d-%02d-%02d', $match[1] == 8 ? 7 : $match[1], $match[2], $match[3], $match[4], $match[5]);
                } else {
                    $this->client_phone = '';
                }
            }
            if (empty($this->client_phone)) {
                $this->client_phone = $this->client_ext_phone ?: '+7 800 301-60-10';
            }
        }

        $this->client_phone = Strings::getPhoneClean($this->client_phone);

        if (!filter_var(mb_strtolower($this->client_email), FILTER_VALIDATE_EMAIL)) {
            $this->client_email = '';
        }

        $name = !empty(trim($this->client_name)) ? $this->client_name : ($this->client_email ?: $this->client_phone);

        //так как импортируются только заказы с тел сайта (как оказалось не только, ибо тел проставляется и заказам не с тел)
        //Но так как пользователи не с тел уже были созданы, то так тоже корректно
        $model->email = $this->client_email;
        $model->username = $this->client_guid;
        $model->password = 'password';
        $model->name = $name;
        $model->isSubscribe = true;
        $model->phone = $this->client_phone;
        $model->bitrix_id = $this->client_bitrix_id;
        $model->guid = $this->client_guid;
        $model->source = UserEmail::SOURCE_SITE_PHONE;
        $model->source_detail = UserEmail::SOURCE_DETAIL_CHECK_ORDER;

        $user = $model->signup();
        if (!$user) {
            Job::dump(print_r($model->getErrors(), true));

            return false;
        }

        return $user;
    }

}