<?php

namespace console\jobs;

/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 07/02/2019
 * Time: 23:28
 */

class UpdatePriceLotJob extends \yii\base\Object implements \yii\queue\Job
{
    public $id;

    public function execute($queue)
    {
        echo 'Start UpdatePriceLotJob id' . $this->id . PHP_EOL;
        \common\helpers\Product::updatePriceLot($this->id);
    }
}