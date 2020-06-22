<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-04-11
 * Time: 13:47
 */

namespace common\models;


use Yii;

class BUFECommABC extends \common\models\generated\models\BUFECommABC
{

    public static function getDb()
    {
        return Yii::$app->dbStat;
    }

}