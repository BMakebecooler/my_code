<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-04-23
 * Time: 18:50
 */

namespace common\models;


use Yii;

class BUFECommPairCTS extends \common\models\generated\models\BUFECommPairCTS
{
    public static function getDb()
    {
        return Yii::$app->dbStat;
    }
}