<?php

namespace console\jobs;

class UpdateSeoJob extends \yii\base\Object implements \yii\queue\Job
{
    public $id;

    public function execute($queue)
    {
        echo 'Start UpdateSeoJob id' . $this->id . PHP_EOL;
        \common\helpers\Product::updateSeoLotWithChilds($this->id);
    }
}