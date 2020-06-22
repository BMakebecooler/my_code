<?php


namespace console\tasks;


class CategoriesHideEmptyTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Скрывает категории, в которых нет товаров';
    public $schedule = '0 0 * * *';

    public function run()
    {
        \Yii::$app->runAction('imports/category/hide-empty-cats');
    }
}