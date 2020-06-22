<?php

namespace console\controllers\queues\jobs\shop;

use console\controllers\queues\jobs\Job;
use console\jobs\UpdateNewFieldsJob;
use modules\shopandshow\models\newEntities\shop\ShopProduct;
use Yii;

class Reserve extends Job
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
            $guid = $this->data['Data']['Guid'];

            Yii::$app->queueProduct->push(new UpdateNewFieldsJob([
                'data' => $queue,
            ]));

            return true;
            return $this->addReserve();
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function addReserve()
    {
        $channelReserve = \Yii::$app->shopAndShowSettings->channelReserveGuid;

        $info = $this->data['Info'];
        $data = $this->data['Data'];

        //Остатки по лотам скипаем ибо проходят какие то странные кол-ва, отрицательные значения при положительных в модификациях
        //Остатки по лотам вычисляем из остатков по модификациям
        if ($info['Type'] == 'ReserveLot') {
            return true;
        }

        if (!$data['Guid']) {
            Job::dump('не указан гуид !');
            return true;
        }

        if ($data['ChannelGuid'] != $channelReserve) {
            Job::dump('не тот channelGuid');
            return true;
        }

        Job::dump('----- Reserve -------');
        Job::dump('Guid: ' . $data['Guid']);
        Job::dump('Quantity: ' . $data['CanSell']);
        Job::dump('QuantityReserved: ' . $data['ReserveValue']);

        $shopProduct = new ShopProduct();

        $shopProduct->guid = trim($data['Guid']);
        $shopProduct->quantity = $data['CanSell'] < 0 ? 0 : $data['CanSell'];
        $shopProduct->quantity_reserved = $data['ReserveValue'];

        return $shopProduct->addData();
    }
}