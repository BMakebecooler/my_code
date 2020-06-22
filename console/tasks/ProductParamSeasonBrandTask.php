<?php


namespace console\tasks;


class ProductParamSeasonBrandTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Подтянуть параметры бренд и сезон из таблицы товара в таблицу параметров фильтра';
    public $schedule = '0 0 * * *';

    public function run()
    {
        \Yii::$app->runAction('param/season-and-brand-props');
    }
}