<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-08-05
 * Time: 12:53
 */

namespace common\models;


use Yii;

class SiteOrder extends \common\models\generated\models\SiteOrder
{

    public static function getDb()
    {
        return Yii::$app->dbStat;
    }

}