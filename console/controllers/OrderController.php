<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-08-05
 * Time: 12:31
 */

namespace console\controllers;


use modules\shopandshow\models\shop\ShopOrder;
use Yii;
use yii\console\Controller;
use yii\db\ActiveRecord;

class OrderController extends Controller
{

    public function actionSendToStat()
    {
        foreach (ShopOrder::find()->select('id')->each() as $shopOrder) {
            echo 'Send to analytics ' . $shopOrder->id . PHP_EOL;

            Yii::$app->queue->push(new \console\jobs\ExportOrderAnalyticsJob([
                'id' => $shopOrder->id,
            ]));
            // duplicate call event
//            $shopOrder->trigger(ActiveRecord::EVENT_AFTER_INSERT);
        }
    }

}