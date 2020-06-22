<?php


namespace console\tasks;


class ProductParamSizeTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Подтянуть параметры размеров из скексовских таблиц в новые';
    public $schedule = '* 0 * * *  ';

    public function run()
    {
        \Yii::$app->runAction('param/last-product-props');
    }
}