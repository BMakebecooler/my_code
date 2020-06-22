<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 21.04.17
 * Time: 10:45
 */

namespace modules\shopandshow\models\shares;


class Types
{


    /**
     * Типы скидок
     */
    const PROMO_TYPE_CP = 1;
    const PROMO_TYPE_SALE = 2;
    const PROMO_TYPE_CTS = 3;
    const PROMO_TYPE_SS = 4;


    const MAIN_WIDE_1 = 1;
    const MAIN_SMALL_EFIR = 2;
    const MAIN_SITE_SALE_1 = 3;
    const MAIN_SITE_SALE_2 = 4;
    const MAIN_SITE_SALE_3 = 5;
    const MAIN_WIDE_2 = 6;


    /**
     * @return array
     */
    public static function getBitrixNameTypes()
    {
        return [
//            self::BANNER_TYPE_0_1 => 'MAIN_WIDE_1',
//            self::BANNER_TYPE_0_2 => 'MAIN_WIDE_1',
//            self::BANNER_TYPE_1 => 'MAIN_SMALL_EFIR',
//            self::BANNER_TYPE_2 => 'MAIN_SMALL_EFIR',
//            self::BANNER_TYPE_3 => 'MAIN_SMALL_EFIR',
//            self::BANNER_TYPE_4 => 'MAIN_SITE_SALE_1',
//            self::BANNER_TYPE_5 => 'MAIN_SITE_SALE_2',
//            self::BANNER_TYPE_6 => 'MAIN_SITE_SALE_3',
//            self::BANNER_TYPE_7 => 'MAIN_WIDE_2',
        ];
    }


    public static function getPromoTypes()
    {
        return [
            self::PROMO_TYPE_CP => 'ЦП (Цена премьеры)',
            self::PROMO_TYPE_SALE => 'РП (Распродажа)',
            self::PROMO_TYPE_CTS => 'ЦТС (Цена только сегодня)',
            self::PROMO_TYPE_SS => 'ШШ (Базовая цена)',
        ];
    }

}