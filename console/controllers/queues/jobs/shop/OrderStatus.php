<?php

namespace console\controllers\queues\jobs\shop;

use console\controllers\queues\jobs\Job;
use modules\shopandshow\models\newEntities\shop\OrderStatus as OrderStatusModel;

class OrderStatus extends Job
{
    /**
     * @param \yii\queue\Queue $queue
     * @param string $guid
     *
     * @return bool
     * @throws \Exception
     */
    public function execute($queue, &$guid)
    {
        if ($this->prepareData($queue)) {
            $guid = $this->data['Data']['OrderGuid'];

            return $this->addStatus();
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function addStatus()
    {
        $info = $this->data['Info'];
        $data = $this->data['Data'];

        if ($data['BPGuid'] != '5E7BA91651501219E0538201090ACBAD') {
            return true;
        }

        Job::dump('----- OrderStatus -------');
        Job::dump('OrderGuid: '.$data['OrderGuid']);
        Job::dump('StatusGuid: '.$data['StatusGuid']);

        $orderStatus = new OrderStatusModel();

        $orderStatus->order_guid = $data['OrderGuid'];
        $orderStatus->status_guid = $data['StatusGuid'];
        $orderStatus->channel_guid = $data['ChannelGuid'];
        $orderStatus->reason_guid = $data['ReasonGuid'];

        return $orderStatus->addData();
    }
}