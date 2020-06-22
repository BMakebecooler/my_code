<?php


namespace console\tasks;


class ImportAnalyticsForBadgesTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Импорт аналитики для плашек';
    public $schedule = '10 */12 * * *';

    public function run()
    {
        \Yii::$app->runAction('product/sync-analytics-for-badges');
    }
}
