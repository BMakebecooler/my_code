<?php


namespace console\tasks;


class ExportFeedYandexTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Генерация фида Yandex';
    public $schedule = '30 */6 * * * ';

    public function run()
    {
        \Yii::$app->runAction('export/yandex-products/feed');
    }
}