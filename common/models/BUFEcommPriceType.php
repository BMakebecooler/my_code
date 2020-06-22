<?php


namespace common\models;


use Yii;

class BUFEcommPriceType extends \common\models\generated\models\BUFEcommPriceType
{
    public static function getDb()
    {
        return Yii::$app->dbStat;
    }
}