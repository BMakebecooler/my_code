<?php


namespace console\tasks;


class SyncProductsTopSixTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Синхронизация товаров аналитики (Топ-6)';
    public $schedule = '0 11 * * *';

    public function run()
    {
        \Yii::$app->runAction('product/sync-products-top-six');
    }
}