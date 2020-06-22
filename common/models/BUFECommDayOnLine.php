<?php


namespace common\models;


use Yii;

class BUFECommDayOnLine extends \common\models\generated\models\BUFECommDayOnLine
{
    public static function getDb()
    {
        return Yii::$app->dbStat;
    }
}