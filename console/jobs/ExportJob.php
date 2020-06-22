<?php

namespace console\jobs;

/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 07/02/2019
 * Time: 23:28
 */

class ExportJob extends \yii\base\Object implements \yii\queue\Job
{
    public $id;

    public function execute($queue)
    {
        echo 'Start job id ' . $this->id . PHP_EOL;
        \Yii::$app->runAction('export/export/run-one', ['id' => $this->id]);
        echo 'End job id ' . $this->id . PHP_EOL;
    }
}