<?php


namespace console\tasks;


class UpdateSortWeightTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Пересчет веса сортировок';
    public $schedule = '*/30 * * * *';

    public function run()
    {
        \Yii::$app->runAction('product/update-sort-weight');
    }
}