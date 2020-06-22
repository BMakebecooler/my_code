<?php


namespace console\tasks;


class SegmentsScheduledGenerationTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Перегенерировать специальные сегменты раз в час';
    public $schedule = '*/30 * * * *';

    public function run()
    {
        \Yii::$app->runAction('segment/scheduled-generation');
    }
}