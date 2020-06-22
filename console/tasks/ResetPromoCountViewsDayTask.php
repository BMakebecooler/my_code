<?php


namespace console\tasks;


class ResetPromoCountViewsDayTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Сбрасывает счетчик просмотров промо страниц раз в сутки';
    public $schedule = '* 0 * * *  ';

    public function run()
    {
        \Yii::$app->runAction('promo/reset-count-views-day');
    }
}