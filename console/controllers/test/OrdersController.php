<?php/** * php ./yii test/orders/send-order * php ./yii test/orders/update-order * php ./yii test/orders/delivery */namespace console\controllers\test;use console\controllers\export\ExportController;use Exception;use modules\shopandshow\lists\Orders;use modules\shopandshow\models\shop\ShopOrder;use modules\shopandshow\models\shop\ShopOrderStatus;use yii\helpers\Console;use uranum\delivery\DeliveryCargoData;use uranum\delivery\DeliveryCalculator;/** * Class OrdersController * * @package console\controllers */class OrdersController extends ExportController{    /**     * Тест доставки     */    public function actionDelivery()    {        $services = \Yii::$app->getModule('delivery')->getComponents();        $data = new DeliveryCargoData(115191, '', 2000, 2000, 275); // zip, locationTo, cartCost, weight, innerCode (own carrier code)        $resultArray = new DeliveryCalculator($data, $services);        var_dump($resultArray->calculate());    }    public function actionSendOrder()    {        $order = ShopOrder::find()            ->innerJoinWith(['user'])//            ->andWhere('shop_order.bitrix_id IS NULL')//            ->andWhere('cms_user.bitrix_id IS NULL')//            ->andWhere('shop_order.status_code != :status' , [//                ':status' => ShopOrderStatus::STATUS_B//            ])            ->andWhere('shop_order.id = 127')            ->one();        if (!$order) {            $this->stdout("No new orders\n", Console::FG_YELLOW);            return true;        }        $this->stdout("Preparing to export order # {$order->id}\n");        $this->stdout("First - send user # {$order->user->id} to Bitrix. ");        \Yii::$app->shopAndShow->sendCreateUserBitrix($order->user);        sleep(5);        $this->stdout("Done\n", Console::FG_GREEN);        $this->stdout("Second - send order # {$order->id} to Bitrix. ");        \Yii::$app->shopAndShow->sendCreateOrderBitrix($order);        $this->stdout("Done\n", Console::FG_GREEN);        $this->stdout("Sleep 3sec\n", Console::FG_YELLOW);    }    public function actionUpdateOrder()    {        $guid = '547F870593064BC4B330437237883F38';        $model = Orders::getOrderByGuid($guid);        if ($model == null) {            throw new Exception("Order guid ID {$guid} not found");        }//        $model->bitrix_id = $this->data->bitrix_id;        /**         * Отвязываемся от ID. Любой номер заказа...         *///        $model->order_number = $this->data->bitrix_id;        $model->setStatus(ShopOrderStatus::STATUS_SUCCESS); //Ставим статус что в удаленной системе заказ принят        if (!$model->validate(['order_number', 'bitrix_id', 'status_code'])) {            throw new Exception("Order model data not valid: " . json_encode($model->getErrors()));        }        if (!$model->save(false, ['order_number', 'bitrix_id', 'status_code'])) {            throw new Exception("Order model data not valid: " . json_encode($model->getErrors()));        }        return true;    }    public function actionNaumenBaskets()    {        $query = <<<SQLSELECT u.phone as u_phone, b.value as b_phone, SUM(so.price) as price, so.sourceFROM shop_order so LEFT JOIN cms_user u ON u.id = so.user_idLEFT JOIN shop_buyer_property b ON b.element_id = so.buyer_id and b.property_id=4WHERE so.created_at between UNIX_TIMESTAMP('2018-03-14 00:00:00') and UNIX_TIMESTAMP('2018-05-05 23:59:59')GROUP BY u.phone, b.value, so.sourceSQL;        $orders = \Yii::$app->db->createCommand($query)->queryAll();        $phones = file(__DIR__.'/files/phones.csv');        $result = array_fill_keys(array_map('trim', $phones), ['site' => 0, 'bitrix' => 0]);        foreach ($orders as $order) {            if ($order['u_phone']) {                $number = $this->parsePhone($order['u_phone']);                if (array_key_exists($number, $result)) {                    $result[$number][$order['source']] += $order['price'];                }            }            if ($order['b_phone']) {                $number = $this->parsePhone($order['b_phone']);                if (array_key_exists($number, $result)) {                    $result[$number][$order['source']] += $order['price'];                }            }        }        $csv = 'phone;site;bitrix'.PHP_EOL;        foreach ($result as $number => $row) {            $csv .= "{$number};{$row['site']};{$row['bitrix']}".PHP_EOL;        }        file_put_contents(__DIR__.'/files/naumen.csv', $csv);    }    public function actionNotNaumenBaskets()    {        $query = <<<SQLSELECT u.phone as u_phone, b.value as b_phone, SUM(so.price) as price, so.sourceFROM shop_order so LEFT JOIN cms_user u ON u.id = so.user_idLEFT JOIN shop_buyer_property b ON b.element_id = so.buyer_id and b.property_id=4WHERE so.created_at between UNIX_TIMESTAMP('2018-03-14 00:00:00') and UNIX_TIMESTAMP('2018-05-05 23:59:59')GROUP BY u.phone, b.value, so.sourceSQL;        $orders = \Yii::$app->db->createCommand($query)->queryAll();        $logsQuery = <<<SQLselect distinct phone from ss_preorders_logswhere created_at between '2018-03-14 00:00:00' and '2018-05-05 23:59:59'SQL;        $logsPhones = \Yii::$app->db->createCommand($logsQuery)->queryAll();        $logsPhonesFormatted = [];        foreach($logsPhones as $logsPhone) {            $number = $this->parsePhone($logsPhone['phone']);            $logsPhonesFormatted[$number] = 0;        }        $phones = file(__DIR__.'/files/phones.csv');        $phones = array_fill_keys(array_map('trim', $phones), 0);        $result = [];        foreach ($orders as $order) {            if ($order['u_phone']) {                $number = $this->parsePhone($order['u_phone']);                if (array_key_exists($number, $logsPhonesFormatted) && !array_key_exists($number, $phones)) {                    @$result[$number][$order['source']] += $order['price'];                }            }            if ($order['b_phone']) {                $number = $this->parsePhone($order['b_phone']);                if (array_key_exists($number, $logsPhonesFormatted) && !array_key_exists($number, $phones)) {                    @$result[$number][$order['source']] += $order['price'];                }            }        }        $csv = 'phone;site;bitrix'.PHP_EOL;        foreach ($result as $number => $row) {            $csv .= "{$number};".($row['site'] ?? 0).";".($row['bitrix'] ?? 0).PHP_EOL;        }        file_put_contents(__DIR__.'/files/not-naumen.csv', $csv);    }    private function parsePhone($phone)    {        return \common\helpers\Strings::onlyInt(str_replace('+7', '8', str_replace(' ', '', $phone)));    }}