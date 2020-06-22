<?php


namespace console\tasks;


class SyncProductsCtsTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Синхронизация допродаж к ЦТС';
    public $schedule = '*/29 * * * *';

    public function run()
    {
        \Yii::$app->runAction('product/sync-products-cts');
    }
}
