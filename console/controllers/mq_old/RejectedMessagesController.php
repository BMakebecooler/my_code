<?php

/**
 * php ./yii mq/rejected-messages
 */

namespace console\controllers\mq;

use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopOrderStatus;
use modules\shopandshow\lists\Orders;
use yii\base\Exception;

/**
 * Class RejectedMessagesController
 *
 * @package console\controllers\mq
 */
class RejectedMessagesController extends ListenerController
{

    public $queueName = 'front.rejected_messages';
    public $routingKey = 'front.order.rejected';

    public function OrderReject()
    {
        if (!isset($this->data->message->guid)) {
            $this->log("data with message->guid not found");

            return;

//            throw new Exception("data with message->guid not found");
        }

        $order = Orders::getOrderByGuid($this->data->message->guid);

        if ($order == null) {
            $this->log("Order with ID {$this->data->message->guid} not found");
            //throw new Exception("Order with ID {$this->data->external_order_id} not found");
            return true;
        }

        $order->setStatus(ShopOrderStatus::STATUS_FAILED); //Ставим статус что в удаленной системе заказ не принят
        $order->incrSendErrorCounter();

        if (!$order->validate(['status_code']))
            return false;

//        throw new Exception("Order model data not valid: " . json_encode($order->getErrors()));

        if (!$order->save(false))
            return false;

//        throw new Exception("Order model data not valid: " . json_encode($order->getErrors()));

        return true;
    }

    public function UserReject()
    {

    }

    /**
     * @param $id
     * @return ShopOrder
     */
    private function getOrderModel($id)
    {
        return ShopOrder::findOne($id);
    }

}