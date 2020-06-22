<?php


namespace common\models;


use Yii;

class BUFECommBrand extends \common\models\generated\models\BUFECommBrand
{
    public static function getDb()
    {
        return Yii::$app->dbStat;
    }
}