<?php
/**
 * Created by PhpStorm.
 * User: andrei
 * Date: 2019-03-18
 * Time: 22:38
 */

namespace common\models\OnAir;


use common\helpers\Dates;
use modules\shopandshow\models\mediaplan\AirBlock;

class Schedule
{

    /**
     * Получить расписание за определенное время
     * @param null $dateTimeFrom
     * @param null $dateTimeTo
     * @return AirBlock[]
     * @throws \Throwable
     */
    public static function findByDateTime($dateTimeFrom = null, $dateTimeTo = null)
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
                ->where(['>=', 'begin_datetime', $dateTimeFrom])
                ->andWhere(['<=', 'end_datetime', $dateTimeTo])
                ->groupBy(['begin_datetime','end_datetime'])
                ->orderBy(['begin_datetime' => SORT_ASC])
                ->all();
        }, MIN_10);
    }

}