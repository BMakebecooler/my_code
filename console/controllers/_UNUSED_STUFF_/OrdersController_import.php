<?php

/**
 * php ./yii imports/orders
 */
namespace console\controllers\imports;

use modules\shopandshow\models\shop\ShopOrder;
use yii\base\Exception;
use yii\helpers\Console;

/**
 * Class OrdersController
 *
 * @package console\controllers
 */
class OrdersControllerImport extends \yii\console\Controller
{

    /** @var Connection */
    protected $frontDb;

    /** @var  Connection */
    protected $db;

    protected $agentStartTime;

    public function beforeAction($action)
    {
        $this->stdout("\nBegin: " . $action->getUniqueId() . "\n\n", Console::FG_YELLOW);

        $this->agentStartTime = time();

        return true;
    }

    public function afterAction($action, $result)
    {
        $this->stdout("\n\nElapsed: " . (time() - $this->agentStartTime) . "sec.\n", Console::FG_YELLOW);

        return parent::afterAction($action, $result);
    }

    public function actionGetRegisteredOrders()
    {

        $ordersToCheck = ShopOrder::find()
            ->select('key')
            ->andWhere('key IS NOT NULL')
            ->andWhere('order_number IS NULL')
            ->asArray()
            ->all();

        if ( $ordersToCheck ) {

            $this->frontDb = \Yii::$app->get('front_db');

            $frontOrders = $this->frontDb->createCommand(
                'SELECT o.id, o.date_insert, op.value as order_key FROM front2.b_sale_order o
                  left join front2.b_sale_order_props_value op ON op.order_id=o.id and op.code = "EXTERNAL_ORDER_KEY"
                 WHERE op.VALUE IN ("'.implode('", ', $ordersToCheck).'")
'
            )->queryAll();

            if ( count($frontOrders) > 0 ) {

                $this->db = \Yii::$app->get('db');

                foreach ( $frontOrders as $order ) {

                    $updateTransaction = $this->db->beginTransaction();

                    try {

                        $updateCommand = $this->db->createCommand(
                            'UPDATE shop_order SET order_number="'. $order['id'].'" WHERE `key`="'.$order['order_key'].'"'
                        );

                        if ( $updateCommand->execute() == 1 ) {

                            /** TODO: отправка письма */

                            $updateTransaction->commit();
                        }
                        else {
                            $updateTransaction->rollback();
                            continue;
                        }

                    } catch (Exception $e) {


                        $updateTransaction->rollback();


                    }

                }


            }

        }

    }

}