<?php


namespace common\models;


use Yii;

class BUFECommProducts extends \common\models\generated\models\BUFECommProducts
{

    public static function getDb()
    {
        return Yii::$app->dbStat;
    }
}