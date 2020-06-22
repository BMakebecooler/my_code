<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-08-02
 * Time: 17:07
 */

namespace console\jobs;


use common\models\ShopOrder;
use common\models\SiteOrder;
use yii\db\Exception;

class ExportOrderAnalyticsJob extends \yii\base\Object implements \yii\queue\Job
{
    public $id;

    public function execute($queue)
    {

        echo 'Start ExportOrderAnalyticsJob ID - ' . $this->id . PHP_EOL;
        $shopOrder = ShopOrder::findOne($this->id);
        $siteOrder = SiteOrder::find()->byOrderId($shopOrder->id)->one();
        if (empty($siteOrder)) {
            $siteOrder = new SiteOrder();
            $siteOrder->order_id = $shopOrder->id;
            $siteOrder->order_created_at = $shopOrder->created_at;
            $siteOrder->order_date = date("Y-m-d H:i:s", $shopOrder->created_at);
        }
        $siteOrder->order_kfss = $shopOrder->order_number;

        if (!$siteOrder->save()) {
            throw  new Exception('Error save siteORder');
        }
        echo 'End ExportOrderAnalyticsJob ID - ' . $this->id . PHP_EOL;
    }
}