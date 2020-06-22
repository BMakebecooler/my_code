<?php

namespace common\helpers;

class Math
{

    /**
     * Округлить с точностью
     * @param $number
     * @param float $precision
     * @return float
     */
    public static function roundingUp($number, $precision = 0.5)
    {
        return ceil($number / $precision) * $precision;
    }

    /**
     * Получаем процент от числа
     * @param $number1
     * @param $number2
     * @return float|int
     */
    public static function percent($number1, $number2)
    {
        if ($number1 && $number2) {
            return ($number2 / $number1) * 100;
        }

        return 0;
    }
}