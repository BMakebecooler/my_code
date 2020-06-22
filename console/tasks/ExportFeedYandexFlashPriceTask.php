<?php


namespace console\tasks;


class ExportFeedYandexFlashPriceTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Генерация фида Yandex (ВыгодаЧаса)';
    public $schedule = '15 * * * * ';

    public function run()
    {
        \Yii::$app->runAction('export/yandex-products/feed-flash-price');
    }
}