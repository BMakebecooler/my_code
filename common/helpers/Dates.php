<?php

/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 24.04.17
 * Time: 14:53
 */

namespace common\helpers;

use \DateTime;
use \Exception;

class Dates
{

    /**
     * Получить timestamp начала дня
     * @param null $timestamp
     * @return false|int
     */
    public static function beginOfDate($timestamp = null)
    {
        $timestamp = ($timestamp) ?: time();
        return strtotime('midnight', $timestamp);
    }

    /**
     * Получить timestamp начала эфирного дня
     * @param null $timestamp
     * @return int
     * @throws Exception
     */
    public static function beginOfAirDate($timestamp = null)
    {
        $timestamp = ($timestamp) ?: time();

        $beginOfDay = (new DateTime())->setTimestamp($timestamp)->format('Y-m-d 07:00:00');

        return (int)(new DateTime($beginOfDay))->format('U');
    }

    /**
     * Получить timestamp окончания дня
     * @param null $timestamp
     * @return false|int
     */
    public static function endOfDate($timestamp = null)
    {
        $timestamp = ($timestamp) ?: time();
        return strtotime('tomorrow', self::beginOfDate($timestamp));

    }

    /**
     * Получить timestamp окончания дня
     * @param null $timestamp
     * @return string
     * @throws Exception
     */
    public static function endOfAirDate($timestamp = null)
    {
        $timestamp = ($timestamp) ?: time();
        $endOfDay = (new DateTime())->setTimestamp($timestamp)->format('Y-m-d 22:00:00');

        return (new DateTime($endOfDay))->format('U');
    }

    /**
     * Получить timestamp начала эфирного периода
     * @param null $timestamp
     * @return int
     */
    public static function beginEfirPeriod($timestamp = null)
    {
        $timestamp = ($timestamp) ?: time();

        return strtotime('midnight + 7 hour', $timestamp);
    }

    /**
     * Получить timestamp окончания эфирного периода
     * @param null $timestamp
     * @return false|int
     */
    public static function endEfirPeriod($timestamp = null)
    {
        $timestamp = ($timestamp) ?: time();
        return strtotime('tomorrow  + 7 hour', self::beginOfDate($timestamp)) - 1;
    }

    public static function timestampToDate($timestamp)
    {
        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * Получить timestamp начала действия баннера
     * @param null $timestamp
     * @return false|int
     */
    public static function beginEfirPeriodForBanner($timestamp = null)
    {
        $timestamp = ($timestamp) ?: time();
        $beginEfirPeriod = self::beginEfirPeriod($timestamp);

        /**
         * Для баннеров такая логика что если текущая дата-время меньше чем дата начала эфира
         * ставим вчерашние баннера
         */
        if ($beginEfirPeriod > $timestamp) {
            return self::beginEfirPeriod(strtotime('yesterday'));
        } else {
            return $beginEfirPeriod;
        }
    }

    /**
     * Получить timestamp начала действия баннера
     * @param null $timestamp
     * @return false|int
     */
    public static function endEfirPeriodForBanner($timestamp = null)
    {
        $timestamp = ($timestamp) ?: time();
        $beginEfirPeriod = self::beginEfirPeriod($timestamp);
        $endnEfirPeriod = self::endEfirPeriod($timestamp);

        if ($beginEfirPeriod > $timestamp) {
            return self::endEfirPeriod(strtotime('yesterday'));
        } else {
            return $endnEfirPeriod;
        }
    }

    /**
     * Конец рабочего периода эфира
     * @return false|int
     */
    public static function endEfirWork()
    {
        return strtotime('midnight  + 22 hour');
    }


    /**
     * Проверка значения на timestamp
     * @param $timestamp
     * @return bool
     */
    public static function isTimestamp($timestamp)
    {
        $check = (is_int($timestamp) || is_float($timestamp))
            ? $timestamp : (string)(int)$timestamp;

        return ($check === $timestamp)
            && ((int)$timestamp <= PHP_INT_MAX)
            && ((int)$timestamp >= ~PHP_INT_MAX);
    }

    /**
     * @param $date
     * @return false|int
     * 0@api
     */
    public static function toTimestamp($date)
    {
        return static::isTimestamp($date) ? $date : strtotime($date);
    }

    /**
     * Входит ли дата в диапазон.
     * @param $value
     * @param $from
     * @param $to
     * @return bool
     */
    public static function between($value, $from, $to)
    {
        return $value >= $from && $value <= $to;
    }

    /**
     * @param $date
     * @return false|int
     */
    public static function dayBeginning($date)
    {
        return strtotime('midnight', self::toTimestamp($date));
    }

    /**
     * @param $date
     * @return false|int
     */
    public static function dayEnd($date)
    {
        return strtotime("+1 day -1 second", self::toTimestamp($date));
    }

    /**
     * получаем Timestamp начала и конца дня
     * @param $date
     * @return array
     */
    public static function dayStartAndEnd($date)
    {
        $start = static::dayBeginning($date);

        return [
            'start' => $start,
            'end' => static::dayEnd($start)
        ];
    }

    public static function getDaytimeFromId($dayId)
    {
        $date = [
            time(),
            strtotime('-1 day'),
            strtotime('-2 day'),
        ];

        return $date[$dayId] ?? $date[0];
    }

    public static function getHourBegin($time = null)
    {
        return strtotime(date("Y-m-d H:00:00", $time ?? time()));
    }

    public static function getHourEnd($time = null)
    {
        return strtotime(date("Y-m-d H:00:00", $time ?? time()) . " +1 hour - 1 second");
    }

    public static function setTimestampToDate($timestamp)
    {
        if($timestamp > 1) {
            $date = new \DateTime();
            $date->setTimestamp($timestamp);
            return $date->format('Y-m-d H:i:s');
        }else{
            return '';
        }
    }
}