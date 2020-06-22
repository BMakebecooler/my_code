<?php


namespace console\tasks;


class UpdateSegmentCatalogHiddenProductsTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Пересчет товаров промо сегментов которые надо скрыть из каталога';
    public $schedule = '0 * * * *';

    public function run()
    {
        \Yii::$app->runAction('segment/update-catalog-hidden-products');
    }
}