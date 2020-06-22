<?php


namespace console\tasks;


class ExportProductsToAnalyticsTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Экспорт товаров (всех сущностей) на сайте';
    public $schedule = '*/15 * * * *';

    public function run()
    {
        \Yii::$app->runAction('product/export-products-to-analytics');
    }
}