<?php


namespace console\tasks;


class SyncPriceTypesTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Пересчет типов цен товаров';
    public $schedule = '15 * * * *';

    public function run()
    {
        \Yii::$app->runAction('product/sync-analytics-price-types');
    }
}
