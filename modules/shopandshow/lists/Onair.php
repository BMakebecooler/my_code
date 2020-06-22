<?php

namespace modules\shopandshow\lists;


use common\helpers\Dates;
use modules\shopandshow\models\mediaplan\AirBlock;
use modules\shopandshow\models\mediaplan\AirDayProductTime;

/**
 * @deprecated
 * @see \common\models\OnAir\OnAir
 * Class Onair
 * @package modules\shopandshow\lists
 */
class Onair
{

    /**
     * @param $startPeriod
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function categories($startPeriod)
    {
        $order = [
            1626,
            1623,
            1622,
            1621,
            1627,
            1625,
            1629,
            1628,
        ];

        return AirDayProductTime::getDb()->cache(function ($db) use ($startPeriod, $order) {
            return AirDayProductTime::find()
                ->select(['ss_mediaplan_air_day_product_time.*', 'count(DISTINCT lot_id) AS count_product'])
//                ->leftJoin('shop_product', 'shop_product.id = ss_mediaplan_air_day_product_time.lot_id')
                ->leftJoin('cms_content_element', 'cms_content_element.id = ss_mediaplan_air_day_product_time.lot_id')
                ->leftJoin('ss_shop_product_prices', 'ss_shop_product_prices.product_id = ss_mediaplan_air_day_product_time.lot_id')
                ->andWhere(
                    'begin_datetime >= :begin_datetime 
                             AND begin_datetime <= :end_datetime 
                             AND new_quantity > 0
                             AND ss_shop_product_prices.price > 0', [
                    ':begin_datetime' => Dates::beginOfDate($startPeriod),
                    ':end_datetime' => Dates::endOfDate($startPeriod),
                ])
                ->groupBy('section_id')
                ->orderBy([new \yii\db\Expression('FIELD (section_id, ' . join(',', $order) . ')')])
                ->having('count_product > 2')
                ->all();
        }, MIN_10);
    }

    /**
     * Получить расписание за определенное время
     * @param null $dateTimeFrom
     * @param null $dateTimeTo
     * @return AirBlock[]
     */
    public static function getScheduleList($dateTimeFrom = null, $dateTimeTo = null)
    {
        if (!$dateTimeFrom) {
            $dateTimeFrom = Dates::beginOfDate(time());
        }

        if (!$dateTimeTo) {
            $dateTimeTo = $dateTimeFrom ? Dates::endOfDate($dateTimeFrom) : Dates::endOfDate(time());
        }

        return AirBlock::getDb()->cache(function ($db) use ($dateTimeFrom, $dateTimeTo) {
            return AirBlock::find()
                ->andWhere('begin_datetime >= :begin_datetime AND end_datetime <= :end_datetime', [
                    ':begin_datetime' => $dateTimeFrom,
                    ':end_datetime' => $dateTimeTo,
                ])
                ->groupBy('begin_datetime, end_datetime')
                ->orderBy('begin_datetime ASC')
                ->all();
        }, MIN_10);
    }

}