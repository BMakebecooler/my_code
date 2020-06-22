<?php
/**
 * Created by PhpStorm.
 * User: andrei
 * Date: 2019-03-17
 * Time: 21:26
 */

namespace common\helpers;


use yii\helpers\Html;

class Icon
{

    /**
     * @param string $xlinkHref
     * @param string $className
     * @return string
     */
    public static function svg($xlinkHref, $className = 'icon')
    {
        return Html::tag('svg', "<use xlink:href='#$xlinkHref'></use>", ['class' => $className]);
    }
}