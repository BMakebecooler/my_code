<?php


namespace console\tasks;


class ExportAbandonedCartsTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Отправка брошенных корзин';
    public $schedule = '*/30 * * * *';

    public function run()
    {
        \Yii::$app->runAction('export/orders/abandoned-via-kfss-api');
    }
}