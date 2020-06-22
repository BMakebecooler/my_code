<?php


namespace console\tasks;


class SegmentSetPromoProductsTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Пересобрать актуальные сборки периодически';
    public $schedule = '* */3 * * *';

    public function run()
    {
        \Yii::$app->runAction('segment/set-promo-products');
    }
}