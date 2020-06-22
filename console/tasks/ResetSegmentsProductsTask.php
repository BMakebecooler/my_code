<?php


namespace console\tasks;


class ResetSegmentsProductsTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Сбрасывает товары промо сегментов';
    public $schedule = '* */6 * * *';

    public function run()
    {
        \Yii::$app->runAction('segment/set-promo-products-queue');
    }
}