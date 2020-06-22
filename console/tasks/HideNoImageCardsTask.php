<?php


namespace console\tasks;


class HideNoImageCardsTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Скрытие товаров (карточек) без изображений';
    public $schedule = '*/30 * * * * ';

    public function run()
    {
        \Yii::$app->runAction('product/hide-no-image-cards');
    }
}