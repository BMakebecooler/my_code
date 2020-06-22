<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-07-01
 * Time: 11:21
 */

namespace console\controllers;


use common\models\generated\models\ShopBasket;
use common\models\generated\models\ShopFuser;
use common\models\QueueLog;
use yii\console\Controller;

class ClearController extends Controller
{

    public function actionFuser()
    {
        $clearDate = time() - DAYS_30 * 6;
        return ShopFuser::deleteAll('created_at < :clear_date', [':clear_date' => $clearDate]);
    }

    public function actionBasket()
    {
        $clearDate = time() - DAYS_30 * 6;
        return ShopBasket::deleteAll('created_at < :clear_date', [':clear_date' => $clearDate]);
    }


    public function actionQueue()
    {
        $clearDate = time() - DAYS_5;
        return QueueLog::deleteAll('created_at < :clear_date', [':clear_date' => $clearDate]);
    }

}