<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-05-06
 * Time: 12:13
 */

namespace common\models;


use Yii;

class BUFECommDop extends \common\models\generated\models\BUFECommDop
{

    public static function getDb()
    {
        return Yii::$app->dbStat;
    }

}