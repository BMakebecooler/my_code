<?php

namespace console\controllers\queues\jobs\shop;

use console\controllers\queues\jobs\Job;
use modules\shopandshow\models\newEntities\shop\OrderPos as OrderPosModel;

class OrderPos extends Job
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

            return $this->addOrderPos();
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function addOrderPos()
    {
        $info = $this->data['Info'];
        $data = $this->data['Data'];

        if ($data['BPGuid'] != '5E7BA91651501219E0538201090ACBAD') {
            return true;
        }

        Job::dump('----- OrderPos -------');
        Job::dump('OrderGuid: '.$data['OrderGuid']);
        Job::dump('ChannelGuid: '.$data['ChannelGuid']);
        Job::dump('positions: '.sizeof($data['Goods']));

        $shopOrderPos = new OrderPosModel();

        $shopOrderPos->order_guid = $data['OrderGuid'];
        $shopOrderPos->channel_guid = $data['ChannelGuid'];
        $shopOrderPos->promo = $data['Promo'];
        $shopOrderPos->goods = $data['Goods'];

        return $shopOrderPos->addData();
    }
}