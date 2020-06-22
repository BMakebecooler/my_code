<?php


namespace console\tasks;


class ExportFeedGoogleFlashPriceTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Генерация фида Google выгода часа';
    public $schedule = '15 * * * * ';

    public function run()
    {
        \Yii::$app->runAction('export/yandex-products/feed-google-flash-price');
    }
}