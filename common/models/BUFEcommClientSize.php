<?php


namespace common\models;

use Yii;

class BUFEcommClientSize extends \common\models\generated\models\BUFEcommClientSize
{
    public static function getDb()
    {
        return Yii::$app->dbStat;
    }
}