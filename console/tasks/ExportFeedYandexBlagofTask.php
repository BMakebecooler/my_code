<?php


namespace console\tasks;


class ExportFeedYandexBlagofTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Генерация фида Yandex для бренда Благоф';
    public $schedule = '0 0 * * * ';

    public function run()
    {
        \Yii::$app->runAction('export/yandex-products/blagof');
    }
}