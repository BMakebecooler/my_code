<?php

/**
 * php ./yii export/metrics
 */

namespace console\controllers\metrics;

use common\helpers\Strings;
use yii\helpers\Console;


/**
 * Class SalesController
 * @package console\controllers
 */
class SalesController extends \yii\console\Controller
{

    public function actionIndex()
    {

        $this->pushSales();

        $this->pushCalls();


    }

    protected function pushSales()
    {

        $sql = <<<SQL
            SELECT 
                UNIX_TIMESTAMP(DATE_FORMAT(o.date_insert, "%Y.%m.%d %H:%i:%s")) as tstamp,
                o.canceled,
                UNIX_TIMESTAMP(DATE_FORMAT(o.date_insert, "%Y.%m.%d")) as create_date,
                UNIX_TIMESTAMP(DATE_FORMAT(o.date_insert, "%Y.%m.%d")) as cancel_date,
                o.price,
                o.price_delivery,
                source.value as source,
                region.value as region,
                town.value as town,
                operator.value as operator,
                channel.value as channel,
                zip.value as zip,
                user.id as client_id,
                UNIX_TIMESTAMP(DATE_FORMAT(user.date_register, "%Y.%m.%d")) as client_register_date
            FROM
                b_sale_order o
            LEFT JOIN b_sale_order_props_value source ON source.order_id=o.id AND source.code='ORDER_SOURCE'
            LEFT JOIN b_sale_order_props_value region ON region.order_id=o.id AND region.code='REGION'
            LEFT JOIN b_sale_order_props_value town ON town.order_id=o.id AND town.code='TOWN'
            LEFT JOIN b_sale_order_props_value operator ON operator.order_id=o.id AND operator.code='ORDER_OPERATOR'
            LEFT JOIN b_sale_order_props_value channel ON channel.order_id=o.id AND channel.code='ORDER_PHONE_OUR'
            LEFT JOIN b_sale_order_props_value zip ON zip.order_id=o.id AND zip.code='ZIP'
            LEFT JOIN b_user user ON user.id=o.user_id
            WHERE
                o.date_insert > '2017-09-24 08:00:00'
                    AND o.DATE_INSERT < '2017-09-25 08:00:00'
            ORDER BY o.id asc;
SQL;

        $sales = \Yii::$app->front_db->createCommand($sql)->queryAll();

        $_sales = [];

        $total = count($sales);

        foreach ($sales as $sale) {

            $point = null;

            $operator_id = Strings::onlyInt($sale['operator']);

            $point = [
                'metricName' => 'sales_24092017',
                'tags' => [
                    'source' => $sale['source'],
                    'canceled' => $sale['canceled'] == 'Y' ? 1 : 0,
                    'canceled_at_same_day' => $sale['create_date'] == $sale['cancel_date'] ? 1 : 0,
                    'region' => $sale['region'],
                    'town' => $sale['region'],
                    'operator' => $sale['operator'],
                    'operator_id' => $operator_id,
                    'phone_our' => $sale['channel'],
                    'zip' => $sale['zip'],
                    'client_first_order' => $sale['create_date'] == $sale['client_register_date'] ? 1 : 0
                ],
                'fields' => [
                    'price' => $sale['price'],
                    'delivery_price' => $sale['price'],
                    'count' => 1
                ],
                'timestamp' => (int) $sale['tstamp']
            ];

            $_sales[] = $point;

            echo "{$total}... ";

            $total--;

        }

        \Yii::$app->metrics->pushHistoryArray($_sales);

    }

    protected function pushCalls()
    {

        $sql = <<<SQL
                select
                    c.cdn as channel,
                    UNIX_TIMESTAMP(c.dt) as call_start,
                    c.loginid as operator_id,
                    c.route,
                    c.callcenter as call_center,
                    c.item_id,
                    e.name as product_name,
                    e.code as lot_num,
                    cs.name as subject
                FROM sands_calls c
                LEFT JOIN sands_call_questions cq ON cq.UF_CALL_ID=c.id
                LEFT JOIN sands_call_subjects cs ON cs.ID=c.sands_call_subjectsID
                LEFT JOIN b_iblock_element e ON e.ID = c.item_id
                WHERE
                c.dt > '2017-09-24 08:00:00'
                    AND c.dt < '2017-09-25 08:00:00';
SQL;

        $calls = \Yii::$app->front_db->createCommand($sql)->queryAll();

        $_calls = [];

        $total = count($calls);

        foreach ($calls as $call) {

            $point = null;

            $point = [
                'metricName' => 'calls_24092017',
                'tags' => [
                    'channel' => $call['channel'],
                    'operator_id' => $call['operator_id'],
                    'route' => $call['route'],
                    'call_center' => $call['call_center'],
                    'lot_num' => $call['lot_num'],
                    'product' => $call['product_name'],
                    'subject' => $call['subject'],
                ],
                'fields' => [
                    'count' => 1
                ],
                'timestamp' => (int) $call['call_start']
            ];

            $_calls[] = $point;

            echo "{$total}... ";

            $total--;

        }

        \Yii::$app->metrics->pushHistoryArray($_calls);

    }



}