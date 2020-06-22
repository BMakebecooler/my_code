<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-08-02
 * Time: 16:30
 */

namespace console\tasks;


class ClearShopBasketTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Очистка корзин';
    public $schedule = '0 2 * * *';

    public function run()
    {
        \Yii::$app->runAction('clear/basket');
    }
}
