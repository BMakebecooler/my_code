<?php


namespace console\tasks;


class ExportFeedFloctoryTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Генерация фида Floctory';
    public $schedule = '10 */6 * * * ';

    public function run()
    {
        \Yii::$app->runAction('export/yandex-products/floctory');
    }
}