<?php

namespace console\controllers\mq;

use common\components\sms\Sms;
use common\helpers\Msg;
use modules\shopandshow\components\amqp\SSMessageBus;
use modules\shopandshow\components\task\SendSurveyMailTaskHandler;
use modules\shopandshow\lists\Orders;
use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopOrderStatus;
use modules\shopandshow\models\task\SsTask;
use modules\shopandshow\services\Survey;
use yii\base\Exception;

/**
 * php ./yii mq/orders/index
 * php ./yii mq/orders/orders-kfss
 * php ./yii mq/orders/orders-status-kfss
 */


/**
 * Class OrdersController
 *
 * @package console\controllers\mq
 */
class OrdersController extends ListenerController
{

    public $queueName = 'front.orders';
    public $routingKey = 'front.order.update';

    /**
     * Для того чтобы слушать кффсс
     */
    public function actionOrdersKfss()
    {

        /** @var SSMessageBus $queue */
        $queue = clone \Yii::$app->frontExchange;
        $queue->queueName = $this->queueName;
        $queue->routingKey = '#';
        $queue->exchangeName = 'OrderCallbackExchange';
        $queue->vhost = '/';

        $queue->messageHandler = function ($id, $message) {

            $this->log("Incoming message. ID: " . $id);
            $this->log("Message body:");
            $this->log($message);

            try {
                $this->parseMessageKfss($message);
            } catch (Exception $e) {
                $this->log("Message decode error");
                $this->log("Exception {$e->getMessage()}");
                var_dump($e->getMessage());
            }

            try {

                if (call_user_func([$this, $this->method]))
                    return true;
                else
                    return false;

            } catch (\Exception $e) {

                $this->log("Message processing error");
                $this->log("Exception: {$e->getMessage()}");

                return false;

            }

        };

        $queue->listen();
    }

    /**
     * Для того чтобы слушать кффсс
     */
    public function actionOrdersStatusKfss()
    {

        /** @var SSMessageBus $queue */
        $queue = clone \Yii::$app->frontExchange;
        $queue->queueName = $this->queueName;
        $queue->routingKey = '#';
        $queue->exchangeName = 'OrderStatusExchange';
        $queue->vhost = '/';

        $queue->messageHandler = function ($id, $message) {

            $this->log("Incoming message. ID: " . $id);
            $this->log("Message body:");
            $this->log($message);

            try {
                $this->parseMessageKfss($message);
            } catch (Exception $e) {
                $this->log("Message decode error");
                $this->log("Exception {$e->getMessage()}");
                var_dump($e->getMessage());
            }

            try {

                if (call_user_func([$this, $this->method]))
                    return true;
                else
                    return false;

            } catch (\Exception $e) {

                $this->log("Message processing error");
                $this->log("Exception: {$e->getMessage()}");

                return false;

            }

        };

        $queue->listen();
    }


    /**
     * @deprecated
     * @return bool
     */
    public function updateOrder()
    {
        $model = Orders::getOrderByGuid($this->data->guid);

        if ($model == null) {
            $this->log("Order with guid {$this->data->guid} not found");
            //throw new Exception("Order with ID {$this->data->external_order_id} not found");
            return true;
        }

//        $model->bitrix_id = $this->data->bitrix_id;

        /**
         * Отвязываемся от ID. Любой номер заказа...
         */

        if (isset($this->data->order_number)) {
            $model->order_number = $this->data->order_number;
        }

        $model->setStatus(ShopOrderStatus::STATUS_SUCCESS); //Ставим статус что в удаленной системе заказ принят

        \Yii::error('updateOrder: '.print_r($this->data, true));

        if (!$model->validate(['status_code', 'order_number'])) {
            return false;
//            throw new Exception("Order model data not valid: " . json_encode($model->getErrors()));
        }

        if (!$model->save(false, ['status_code', 'order_number'])) {
            return false;
//            throw new Exception("Order model data not valid: " . json_encode($model->getErrors()));
        }

        $this->sendSms($model);

        return true;
    }


    /**
     * @param ShopOrder $order
     * @return bool
     */
    private function sendSms(ShopOrder $order)
    {

        if (!$order->order_number) {
            return false;
        }

        $text = sprintf('Номер Вашего заказа №%s. Ожидайте звонка оператора.', $order->order_number);

        if ($order->user->isApprovePhone()) {
            $phone = $order->user->phone;

            return \Yii::$app->sms->sendSms($phone, $text, true, Sms::SMS_TYPE_CREATE_ORDER);
        }

        return false;

    }

    /**
     * @return bool
     */
    protected function setCallbackOrderByKfss()
    {

        $guid = $this->data['OrderGuid'];

        $model = Orders::getOrderByGuid($guid);

        if ($model == null) {
            $this->log("Order with guid {$guid} not found");
            //throw new Exception("Order with ID {$this->data->external_order_id} not found");
            return true;
        }

//        $model->bitrix_id = $this->data->bitrix_id;

        if ($model->status_code == ShopOrderStatus::STATUS_SEND_QUEUE) {
            $model->setStatus(ShopOrderStatus::STATUS_SUCCESS); //Ставим статус что в удаленной системе заказ принят

            /**
             * Отвязываемся от ID. Любой номер заказа...
             */

            if (isset($this->data['OrderNumber'])) {
                $model->order_number = $this->data['OrderNumber'];
            }

            if (!$model->validate(['status_code', 'order_number'])) {
                return false;
                //            throw new Exception("Order model data not valid: " . json_encode($model->getErrors()));
            }

            if (!$model->save(false, ['status_code', 'order_number'])) {
                return false;
                //            throw new Exception("Order model data not valid: " . json_encode($model->getErrors()));
            }

            // \Yii::error("Заказ {$model->id} принят в удаленной системе (шлем смс)");

            $this->sendSms($model);

            if ($model->user && $model->user->email) {
                \Yii::$app->mailer->compose('modules/shop/client_new_order', [
                    'order' => $model,
                ])
                    ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                    ->setTo($model->user->email)
                    ->setSubject(\Yii::$app->cms->appName . ': ' . \Yii::t('skeeks/shop/app', 'New order'))
                    ->send();
            }
        }

        return true;

    }

    /**
     * @return bool
     */
    protected function setStatusOrderByKfss()
    {
        //Список статусов так же присутствует в импорте исторических статусов заказов
        //console/controllers/imports/ShopOrderController.php :: actionImportOrdersStatusesHistory

        static $statusMap = [
            '5E7C4565A2F0EE82E0538201090A6A04' => ShopOrderStatus::STATUS_CANCELED, // Отменен Заказ отменен 0
            '6D7EF31FC0ADF100E0538201090A3BB8' => ShopOrderStatus::STATUS_CANCELED, // Есть возврат По данному заказу есть возврат товара 24
            '6D82A5F81C42614EE0538201090A354F' => ShopOrderStatus::STATUS_COMPLETED, // Вручен клиенту Заказ вручен получателю, но оплата еще не поступила 23

            '5E7BB735E592C108E0538201090ADABA' => ShopOrderStatus::STATUS_CHECKED, // Проверен Заказ прошел проверку 2
            '5E7BB735E594C108E0538201090ADABA' => ShopOrderStatus::STATUS_READY, // Готов к формированию отправлений Заказ готов к формированию отправлений 5
            '5E7BA91651561219E0538201090ACBAD' => ShopOrderStatus::STATUS_TRAVEL, // Отправлен - ждет оплаты Клиенту отправлены посылки - ждем оплаты 10
            '5E7BA91651591219E0538201090ACBAD' => ShopOrderStatus::STATUS_TRAVEL, // Оплачен и отправлен За заказ полностью получена оплата и он отправлен клиенту  13

            //Если что то нужно из нижеследующего - назначить букву и раскоментить
//            '5E7BF7E8C7CED4A8E0538201090A2AA2' => '', //Создается Заказ находится в процессе ввода
//            '5E7C4565A2F3EE82E0538201090A6A04' => '', //Не прошел проверку Возникла ошибка при проверке
//            '5E7BA91651611219E0538201090ACBAD' => '', //Дополнительная проверка Статус для дополнительной проверки заказа

//            '5E7BA91651541219E0538201090ACBAD' => '', //Нехватка товара при обеспечении Недостаточно товарных остатков для обеспечения заказа
//            '5E7BA91651551219E0538201090ACBAD' => '', //Полностью готовы отправления Весть товар для заказа включен в отправления
//            '6B547CC9B4804C35E0538201090A9591' => '', //Передается на производство Данные по заказу отправлены на производство, но ответ еще не получен
//            '5E7BA916515E1219E0538201090ACBAD' => '', //На производстве Посылки для заказа находятся на производстве

        ];

        $statusGuid = $this->data['StatusGuid'];
        if (!array_key_exists($statusGuid, $statusMap)) {
            return true;
        }

        $channelGuid = $this->data['ChannelGuid'];
        if ($channelGuid != \Yii::$app->shopAndShowSettings->channelSaleGuid) {
            return true;
        }

        $guid = $this->data['OrderGuid'];

        $model = Orders::getOrderByGuid($guid);

        if ($model == null) {
            //$this->log("Order with guid {$guid} not found");
            //throw new Exception("Order with ID {$this->data->external_order_id} not found");
            return true;
        }


        $model->setStatus($statusMap[$statusGuid]); //Ставим обновленный статус

        if ($model->isAttributeChanged('status_code')) {
            if (!$model->validate(['status_code'])) {
                \Yii::error('setStatusOrderByKfss failed to validate status '.print_r($model->getErrors(), true));
                return false;
                //            throw new Exception("Order model data not valid: " . json_encode($model->getErrors()));
            }

            if ($model->status_code == ShopOrderStatus::STATUS_CANCELED) {
                $model->reason_canceled = $this->data['ReasonGuid'];
            }

            if (!$model->save(false, ['status_code', 'reason_canceled'])) {
                \Yii::error('setStatusOrderByKfss failed to save status '.print_r($model->getErrors(), true));
                return false;
            }

            //TODO Включить когда старый сайт перестанет слушать очереди и слать данную инфу!
            //Отключено что бы оба сайта не слалили одно и то же
            if (false) {
                if ($model->status_code == ShopOrderStatus::STATUS_COMPLETED) {
                    Survey::sendSurvey(Survey::ORDER_COMPLETE_TYPE, $model);
                } elseif ($model->status_code == ShopOrderStatus::STATUS_CANCELED) {
                    if (time() - $model->created_at < DAYS_2) {
                        Survey::sendSurvey(Survey::ORDER_CANCEL_TYPE, $model);
                    }
                }
            }

        }

        return true;

    }

}