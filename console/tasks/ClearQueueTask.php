<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-08-02
 * Time: 16:30
 */

namespace console\tasks;


class ClearQueueTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Очистка лога обмена';
    public $schedule = '0 1 * * *';

    public function run()
    {
        \Yii::$app->runAction('clear/queue');
    }
}
