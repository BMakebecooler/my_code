<?php

namespace console\controllers\sync\helpers;

/**
 * Class SyncHelper
 *
 * @package console\controllers
 */
class SyncHelper
{

    const
        BITRIX_PRODUCT_IBLOCK_ID = 10,
        BITRIX_OFFERS_IBLOCK_ID  = 11;

    public static function getCleanName($string){

        $string = preg_replace('/^\[[0-9\-]+\]/xi', '', $string);
        $string = preg_replace('/\s\(\d+\)$/xi', '', $string);
        $string = htmlspecialchars(trim($string));

        return $string;

    }

}