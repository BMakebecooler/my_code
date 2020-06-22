<?php

namespace console\jobs;

use common\components\queue\Factory;

/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 07/02/2019
 * Time: 23:28
 */
class UpdateNewFieldsJob extends \yii\base\Object implements \yii\queue\Job
{

    public $data;

    public function execute($queue)
    {
        echo 'Start UpdateNewFieldsJob' . PHP_EOL;
        $handler = Factory::factory($this->data);
        echo 'Execute ' . get_class($handler) . PHP_EOL;
        $handler->execute();
        echo 'End UpdateNewFieldsJob' . PHP_EOL;
    }
}