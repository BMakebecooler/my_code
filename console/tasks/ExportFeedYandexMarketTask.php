<?php


namespace console\tasks;


class ExportFeedYandexMarketTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Генерация фида Yandex Маркет';
    public $schedule = '0 1-23/2 * * *';//каждые 2 часа

    public function run()
    {
        \Yii::$app->runAction('export/yandex-products/market');
    }
}