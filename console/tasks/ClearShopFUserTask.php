<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-08-02
 * Time: 16:30
 */

namespace console\tasks;


class ClearShopFUserTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Очистка ShopFUser-еров';
    public $schedule = '0 3 * * *';

    public function run()
    {
        \Yii::$app->runAction('clear/fuser');
    }
}
