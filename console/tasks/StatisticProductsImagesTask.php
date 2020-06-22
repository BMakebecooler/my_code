<?php


namespace console\tasks;


class StatisticProductsImagesTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Подсчет статистики карточек товара без фото';
    public $schedule = '0 0 * * *';

    public function run()
    {
        \Yii::$app->runAction('statistic/set-products-without-images-count');
    }
}