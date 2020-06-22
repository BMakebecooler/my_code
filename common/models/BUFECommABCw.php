<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-04-19
 * Time: 16:27
 */

namespace common\models;


use Yii;

class BUFECommABCw extends \common\models\generated\models\BUFECommABCw
{
    public static function getDb()
    {
        return Yii::$app->dbStat;
    }
}