<?php


namespace console\tasks;


class CategoriesUpdatePopularityTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Обновляет популярность категорий для меню';
    public $schedule = '0 0 * * *';

    public function run()
    {
        \Yii::$app->runAction('imports/category/update-popularity');
    }
}