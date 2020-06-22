<?php


namespace console\tasks;


class SegmentsSortByQtyTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Обновить сортировку в сегментах по  доступным размерам';
    public $schedule = '*/30 * * * * ';

    public function run()
    {
        \Yii::$app->runAction('segment/sort-by-qty');
    }
}