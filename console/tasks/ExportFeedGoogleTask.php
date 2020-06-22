<?php


namespace console\tasks;


class ExportFeedGoogleTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Генерация фида Google';
    public $schedule = '10 */6 * * * ';

    public function run()
    {
        \Yii::$app->runAction('export/yandex-products/feed-google');
    }
}