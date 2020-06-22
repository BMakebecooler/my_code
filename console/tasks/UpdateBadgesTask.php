<?php


namespace console\tasks;


class UpdateBadgesTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Пересчет плашек';
    public $schedule = '*/15 * * * *';

    public function run()
    {
        \Yii::$app->runAction('product/update-badges');
    }
}
