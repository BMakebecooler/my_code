<?php


namespace console\tasks;


class ExportFeedCriteoTask extends \webtoolsnz\scheduler\Task
{

    public $description = 'Генерация фида Criteo';
    public $schedule = '35 */2 * * *';

    public function run()
    {
        \Yii::$app->runAction('export/yandex-products/criteo');
    }
}