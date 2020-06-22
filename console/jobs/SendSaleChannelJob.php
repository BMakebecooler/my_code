<?php

namespace console\jobs;

use common\components\sale\SaleFactory;

/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 07/02/2019
 * Time: 23:28
 */

class SendSaleChannelJob extends \yii\base\Object implements \yii\queue\Job
{

    public $channelClass;

    public $orderId;

    public $label;

    public function execute($queue)
    {
        echo 'Start SendSaleChannelJob' . PHP_EOL;
        $channel = SaleFactory::createChannel($this->channelClass);
        $channel->trackCheckout($this->orderId,$this->label);
        echo 'End SendSaleChannelJob' . PHP_EOL;
    }
}