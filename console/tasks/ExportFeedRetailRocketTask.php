<?php


namespace console\tasks;


class ExportFeedRetailRocketTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Генерация фида RetailRocket';
    public $schedule = '20 */6 * * * ';

    public function run()
    {
        \Yii::$app->runAction('export/yandex-products/retail-rocket');
    }
}