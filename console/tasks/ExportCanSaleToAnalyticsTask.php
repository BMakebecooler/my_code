<?php


namespace console\tasks;


class ExportCanSaleToAnalyticsTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Экспорт товаров (модификаций) продающихся на сайте';
    public $schedule = '0 9 * * *';

    public function run()
    {
        \Yii::$app->runAction('export/products/can-sale-to-analytics');
    }
}