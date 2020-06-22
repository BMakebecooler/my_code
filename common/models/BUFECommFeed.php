<?php


namespace common\models;


use Yii;

class BUFECommFeed extends \common\models\generated\models\BUFECommFeed
{
    public static function getDb()
    {
        return Yii::$app->dbStat;
    }
}