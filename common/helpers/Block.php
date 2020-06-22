<?php


namespace common\helpers;


class Block
{
    public static $blockTypes = [
        'cts' => 'ЦТС',
        'slider_products' => 'Слайдер товаров',
        'schedule' => 'Расписание',
        'categories' => 'Категории',
        'two_banners' => 'Банера 2 шт',
        'three_banners' => 'Банера 3 шт',
        'air' => 'Эфир'
    ];

    public static function getBlockTypes()
    {
        return self::$blockTypes;
    }
}