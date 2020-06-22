<?php


namespace console\tasks;


class ClearProductsSliderAllTask extends \webtoolsnz\scheduler\Task
{

    public $description = 'Очистить картинки из не моды во всех слайдерах';
    public $schedule = '0 0 * * *';

    public function run()
    {
        \Yii::$app->runAction('tools/products/clear-tn-product-slider');
    }
}