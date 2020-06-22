<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-04-11
 * Time: 13:47
 */

namespace common\models;


use Yii;

class BUFECommTop6Lots extends \common\models\generated\models\BUFECommTop6Lots
{

    public static function getDb()
    {
        return Yii::$app->dbStat;
    }

}