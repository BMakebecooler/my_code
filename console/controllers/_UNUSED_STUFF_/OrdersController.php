<?php

/**
 * php ./yii sync/orders
 */

namespace console\controllers\sync;

use modules\shopandshow\models\shop\ShopOrder;
use yii\db\Exception;
use yii\helpers\Console;


/**
 * Class StockController
 *
 * @package console\controllers
 */
class OrdersController extends SyncController
{

    public function actionIndex()
    {

        $orders = ShopOrder::find()
            ->andWhere('bitrix_id IS NULL')
            ->all();

        if ( $orders === NULL ) {
            $this->stdout("No new orders\n", Console::FG_YELLOW);
            return true;
        }

        $tmp = [];

        foreach ($orders as $k => $order)
            $tmp[$order->key] = $order;

        $localCount = count($tmp);

        $this->stdout("Got {$localCount} to link\n", Console::FG_YELLOW);

        $query = "
            select order_id as front_order_id, value as order_key from front2.b_sale_order_props_value where
            value is not null
            and code='EXTERNAL_ORDER_KEY'
        ";

        $frontOrders = \Yii::$app->db->createCommand($query)->queryAll();

        $syncCount = count($frontOrders);

        $this->stdout("Got {$syncCount} to sync\n", Console::FG_YELLOW);

        foreach ($frontOrders as $fo)
        {

            if ( !array_key_exists($fo['order_key'], $tmp) ) {
                $this->stdout("LOCAL ORDER MISMATCH: ".json_encode($fo)."\n", Console::FG_RED);
                continue;
            }

            /** @var ShopOrder $_localOrder */
            $_localOrder = &$tmp[$fo['order_key']];

            $_localOrder->bitrix_id = (int) $fo['front_order_id'];

            try {

                $this->stdout("Processing order ID {$_localOrder->id}\n", Console::FG_YELLOW);

                if ( $_localOrder->save() ) {

                    /** TODO: подумать над организацией html+текстовые шаблоны писем
                        пока сделал так
                     */
                    \Yii::$app->mailer->htmlLayout = false;
                    \Yii::$app->mailer->textLayout = false;

                    \Yii::$app->mailer->compose('modules/shop/client_new_order', [
                        'model'  => $_localOrder,
                    ])

                    /** TODO: вынести все в настройки - почта, сабж */
                    ->setFrom(['newsite@shopandshow.ru' => 'Shop & show'])
                    ->setTo([ $_localOrder->user->email => $_localOrder->user->name ])
                    ->setSubject('Ваш заказ зарегистрирован')
                    ->send();

                }

            } catch (Exception $e) {
                $this->logError($e, $_localOrder->attributes);
                continue;
            }

        }

        return true;

    }

}