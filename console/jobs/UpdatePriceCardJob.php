<?php

namespace console\jobs;

/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 07/02/2019
 * Time: 23:28
 */

class UpdatePriceCardJob extends \yii\base\Object implements \yii\queue\Job
{
    public $id;

    /**
     * @param \yii\queue\Queue $queue
     * @throws \yii\db\Exception
     */
    public function execute($queue)
    {
        echo 'Start UpdatePriceCardJob id' . $this->id . PHP_EOL;
        \common\helpers\Product::updatePriceCard($this->id);
    }
}