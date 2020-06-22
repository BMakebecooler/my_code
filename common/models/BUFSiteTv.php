<?php

namespace common\models;


use Yii;

class BUFSiteTv extends \common\models\generated\models\BUFSiteTv
{

    public static function getDb()
    {
        return Yii::$app->dbStat;
    }

}