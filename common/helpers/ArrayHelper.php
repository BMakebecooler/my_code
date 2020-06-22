<?php

/**
 * Помошник для работы с массивами
 * User: koval
 * Date: 01.03.17
 * Time: 12:18
 */

namespace common\helpers;


class ArrayHelper extends \yii\helpers\ArrayHelper
{

    /**
     * Получить только нужные ключи из массива
     * @param array $array
     * @param array $keys
     * @param bool $isReturnValues
     * @return array
     */
    public static function getArrayKeys(array $array, array $keys, $isReturnValues = false)
    {
        $ik = array_intersect_key($array, array_flip($keys));

        return $isReturnValues ? array_values($ik) : $ik;
    }


    /**
     * Конвертировать все значения массива в инт
     * @param array $array
     * @return array
     */
    public static function arrayToInt(array $array)
    {
        return array_map('intval', $array);
    }

    /**
     * Конвертировать все значения массива во float
     * @param array $array
     * @return array
     */
    public static function arrayToFloat(array $array)
    {
        return array_map('floatval', $array);
    }


    /**
     * Конвертировать все значения массива в строку
     * @param $array
     * @return array
     */
    public static function arrayToString($array)
    {
        return array_map(function ($v) {
            return sprintf('"%s"', $v);
        }, $array);
    }

    /**
     * Преобразовать массив в "простой" список
     * @param array $array
     * @return array
     */
    public static function arrayFlatten(array $array)
    {
        $flatten = [];
        array_walk_recursive($array, function ($a) use (&$flatten) {
            $flatten[] = $a;
        });

        return $flatten;
    }

    public static function arraySumColumn($array, $column)
    {
        return array_sum(array_column($array, $column));
    }

    /**
     * Writes a value into an associative array at the key path specified.
     * If there is no such key path yet, it will be created recursively.
     * If the key exists, it will be overwritten.
     *
     * ```php
     *  $array = [
     *      'key' => [
     *          'in' => [
     *              'val1',
     *              'key' => 'val'
     *          ]
     *      ]
     *  ];
     * ```
     *
     * The result of `ArrayHelper::setValue($array, 'key.in.0', ['arr' => 'val']);` will be the following:
     *
     * ```php
     *  [
     *      'key' => [
     *          'in' => [
     *              ['arr' => 'val'],
     *              'key' => 'val'
     *          ]
     *      ]
     *  ]
     *
     * ```
     *
     * The result of
     * `ArrayHelper::setValue($array, 'key.in', ['arr' => 'val']);` or
     * `ArrayHelper::setValue($array, ['key', 'in'], ['arr' => 'val']);`
     * will be the following:
     *
     * ```php
     *  [
     *      'key' => [
     *          'in' => [
     *              'arr' => 'val'
     *          ]
     *      ]
     *  ]
     * ```
     *
     * @param array $array the array to write the value to
     * @param string|array|null $path the path of where do you want to write a value to `$array`
     * the path can be described by a string when each key should be separated by a dot
     * you can also describe the path as an array of keys
     * if the path is null then `$array` will be assigned the `$value`
     * @param mixed $value the value to be written
     * @since 2.0.13
     */
    public static function setValue(&$array, $path, $value)
    {
        if ($path === null) {
            $array = $value;
            return;
        }
        $keys = is_array($path) ? $path : explode('.', $path);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($array[$key])) {
                $array[$key] = [];
            }
            if (!is_array($array[$key])) {
                $array[$key] = [$array[$key]];
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;
    }

    /*
     * Перевод одномерного массива в таблицу (например для характеристик товара)
     */
    public static function arrayPropsToTable($properties)
    {
        $str = '';
        if ($properties) {
            foreach ($properties as $property) {
                $str .= "
<tr>
    <td style='width: 33%;'>{$property['PropertyName']}</td>
    <td>{$property['PropertyValue']}</td>
</tr>";
            }

            $str = "
<table class='table table-striped table-sm mb-0'>
{$str}
</table>";
        }
        return $str;
    }

    public static function clearArray($array = [])
    {
        $array = array_diff($array, []);
        foreach ($array as &$part){
            $part= ltrim(rtrim($part));
        }

        //очищаем массив от пустых элементов
        $new_array = array_filter($array, function($element) {
            return !empty($element);
        });

        return $new_array;
    }
}