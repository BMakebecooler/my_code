<?php


namespace common\components\sale;

use console\jobs\SendSaleChannelJob;
use Yii;
use skeeks\cms\shop\models\ShopOrder;

class SaleFactory
{
    protected static $channelLabels = [
        'admitad_uid' => 'AdmitadChannel',
//        'new_label' => 'TestChannel'
    ];

    public static function setLabelsData()
    {
        foreach (self::$channelLabels as $label => $channelClass) {
            $channel = self::createChannel($channelClass);
            $channel->setLabelData();
        }
        return true;
    }

    public static function trackCheckout(ShopOrder $order)
    {
        foreach (self::$channelLabels as $label => $channelClass) {
            $channel = self::createChannel($channelClass);
            $labelData = $channel->getLabelData();

            if ($labelData) {
//                Yii::$app->queue->push(new SendSaleChannelJob([
//                    'channelClass' => $channelClass,
//                    'orderId' => $order->id,
//                    'label' => $labelData,
//                ]));

                $channel->trackCheckout($order->id, $labelData);
                $channel->deleteLabelData();
            }
        }
        return true;
    }

    public static function createChannel(string $class)
    {
        $class = '\\common\\components\\sale\\channels\\' . $class;
        return Yii::createObject($class);
    }
}