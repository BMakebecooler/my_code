<?php


namespace console\tasks;


class ProductParamColorTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Подтянуть параметры цветов из скексовских таблиц в новые';
    public $schedule = '* 0 * * *';

    public function run()
    {
        \Yii::$app->runAction('param/new-props-catalog-color');
    }
}