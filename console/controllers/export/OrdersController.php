<?php

/**
 * php ./yii export/orders/export
 * php ./yii export/orders/order-export 187336
 * php ./yii export/orders/send-to-super-vizer
 * php ./yii export/orders/new-export-kfss
 * php ./yii export/orders/abandoned-baskets
 *
 * php ./yii export/orders/abandoned-via-kfss-api [USER_ID]
 */

namespace console\controllers\export;

use common\components\rbac\CmsManager;
use common\helpers\Msg;
use common\helpers\User;
use common\models\query\ShopFuserQuery;
use common\models\ShopFuser;
use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopOrderStatus;
use skeeks\modules\cms\money\currency\PHP;
use yii\helpers\Console;


/**
 * Class OrdersController
 * @package console\controllers
 */
class OrdersController extends ExportController
{

    /**
     * Максимальное кол-во попыток отправки заказа
     */
    const COUNT_MAX_ERROR_QUEUE = 1;

    const MIN_ORDER_ID = 1310;
    const MAX_ORDER_ID = 1310;

    public function actionExport()
    {
        //$this->sendToQueue();
        $this->actionExportKfss();
        //$this->actionExportKfssViaApi();
    }

    /**
     * Отправляем заказ по ид
     * @param $orderId
     * @return bool
     */
    public function actionOrderExport($orderId)
    {
        $order = ShopOrder::findOne($orderId);

        if (!$order) {
            $this->stdout("No new orders\n", Console::FG_YELLOW);
            return true;
        }

        $this->stdout("Preparing to export order # {$order->id}\n");

        \Yii::$app->shopAndShow->sendCreateOrder($order);
        \Yii::$app->shopAndShow->sendCreateOrderPositions($order);

        $order->setStatus(ShopOrderStatus::STATUS_SEND_QUEUE);

        $order->incrSendCounter(); //Прибавляем счетчик отправленных
        $order->updateLastSendQueueAt(); //Обновляем дату последней отправки

        $order->save(false);

        $this->stdout("Done\n", Console::FG_GREEN);
    }


    /**
     * Отправка заказов по новой системе в КФСС
     */
    public function actionExportKfss()
    {
        $orders = $this->getNewOrders();

        if (!$orders) {
            $this->stdout("No new orders\n", Console::FG_YELLOW);
            return true;
        }

        /**
         * @var $order ShopOrder
         */
        foreach ($orders as $order) {

            try {

                if (!$order->guid_id) {
                    $this->stdout("order {$order->id} set guid \n", Console::FG_GREEN);
                    $order->guid->generateGuid();
                }

                if (!$order->user->guid_id) {
                    $this->stdout("user {$order->user->id} set guid \n", Console::FG_GREEN);
                    $order->user->guid->generateGuid();
                }

                $this->stdout("Preparing to export order # {$order->id}\n");

                //* Проверяем, нужно ли отправлять заказ текущего пользователя *//

                if (!User::hasRole($order->user_id, CmsManager::ROLE_DEMO)) {
                    $this->stdout("Send order # {$order->id}\n");
                    //Не демо пользователь - отправляем заказ
                    \Yii::$app->shopAndShow->sendCreateOrder($order);
                    \Yii::$app->shopAndShow->sendCreateOrderPositions($order);

                    $order->setStatus(ShopOrderStatus::STATUS_SEND_QUEUE);
                } else {
                    //Заказ от демо юзера - сразу ставим статус что все уже в удаленной системе
                    $this->stdout("Skip order send #{$order->id} for user #{$order->user_id}" . PHP_EOL, Console::FG_CYAN);
                    $order->setStatus(ShopOrderStatus::STATUS_SUCCESS);
                }

                //* /Проверяем, нужно ли отправлять заказ текущего пользователя *//

                $order->incrSendCounter(); //Прибавляем счетчик отправленных
                $order->updateLastSendQueueAt(); //Обновляем дату последней отправки

                $order->save(false);

//                $this->sendToEmailAdmin($order);
//                $this->sendToGetresponseSubscribe($order, \Yii::$app->params['getresponse']['tokens']['subscription']);

                $this->stdout("Done\n", Console::FG_GREEN);

            } catch (\Exception $e) {
                $this->stdout("error send order {$order->id}\n" . $e->getTraceAsString(), Console::FG_RED);
                \Yii::error("error send order {$order->id}\n" . $e->getMessage());
                var_dump($e->getMessage());
            }

        }

    }

    /**
     * Отправка брошенных корзин
     */
    public function actionAbandonedBaskets()
    {
        //ToDo пока надо отключить эту функцию изза проблем с коллцентром
        return true;

        $testerPhone = 9265609162;

        $sql = $this->getAbandonedBasketSql();

        $data = \Yii::$app->db->createCommand($sql)->queryAll();

        $insertSql = <<<SQL
INSERT IGNORE INTO ss_preorders_logs (phone, products, products_ids)
SQL;
        $ordersNum = \Yii::$app->db->createCommand($insertSql . $sql)->execute();

        $this->stdout("Брошенных корзин обработано (записано в лог): {$ordersNum}" . PHP_EOL, Console::FG_RED);
        $this->stdout("Отправляю в очередь брошенных корзин: " . count($data) . PHP_EOL, Console::FG_RED);

        foreach ($data as $row) {
            try {
                //* Проверяем, нужно ли отправлять заказы текущего пользователя *//
                //* Исключим пользователя автотеста что бы заказы не отправлялись *//
                $phone = (int)substr(\common\helpers\Strings::onlyInt($row['PROP_PHONE']), -10);
                if ($phone == $testerPhone || User::hasRoleByPhone($row['PROP_PHONE'], CmsManager::ROLE_DEMO)) {
                    continue;
                }
                //* /Исключим пользователя автотеста что бы заказы не отправлялись *//

                \Yii::$app->shopAndShow->sendAbandonedBaskets($row);
            } catch (\Throwable $e) {
                $this->stdout("error send abandoned baskets for phone: {$row['PROP_PHONE']}" . PHP_EOL, Console::FG_RED);
                var_dump($e->getMessage());
            }
        }

        $this->stdout("Done" . PHP_EOL, Console::FG_GREEN);
    }

    /**
     * Отправляем в очередь
     * @return bool
     */
    public function actionSendToSuperVizer()
    {

        return true;

        $orders = ShopOrder::find()
            ->innerJoinWith(['user'])
            ->andWhere('shop_order.id > :min_order_id AND shop_order.id <= :max_order_id',
                [
                    ':min_order_id' => self::MIN_ORDER_ID,
                    ':max_order_id' => self::MAX_ORDER_ID,
                ])
            ->all();

        /**
         * @var $order ShopOrder
         */
        foreach ($orders as $order) {
            try {
                $this->sendToGetresponseSuperVizer($order, 'g');
            } catch (\Exception $e) {
                $this->stdout("error send order {$order->id}\n", Console::FG_RED);
            }
        }
    }


    /**
     * Отправляем в очередь
     * @return bool
     */
    protected function sendToQueue()
    {

        $orders = $this->getOrders();

        if (!$orders) {
            $this->stdout("No new orders\n", Console::FG_YELLOW);
            return true;
        }

        /**
         * @var $order ShopOrder
         */
        foreach ($orders as $order) {

            try {


                if (!$order->guid_id) {
                    $this->stdout("order {$order->id} set guid \n", Console::FG_GREEN);
                    $order->guid->generateGuid();
                }

                if (!$order->user->guid_id) {
                    $this->stdout("user {$order->user->id} set guid \n", Console::FG_GREEN);
                    $order->user->guid->generateGuid();
                }

                $this->stdout("Preparing to export order # {$order->id}\n");
                $this->stdout("First - send user # {$order->user->id} to Bitrix. ");

                \Yii::$app->shopAndShow->sendCreateUserBitrix($order->user);

                $this->stdout("User Done\n", Console::FG_GREEN);
                $this->stdout("Second - send order # {$order->id} to Bitrix. ");

                \Yii::$app->shopAndShow->sendCreateOrderBitrix($order);

                $order->setStatus(ShopOrderStatus::STATUS_SEND_QUEUE);

                $order->incrSendCounter(); //Прибавляем счетчик отправленных
                $order->updateLastSendQueueAt(); //Обновляем дату последней отправки

                $order->save(false);

                $this->sendToEmailAdmin($order);
                $this->sendToGetresponseSubscribe($order, \Yii::$app->params['getresponse']['tokens']['subscription']);

                $this->stdout("Done\n", Console::FG_GREEN);
                $this->stdout("Sleep 3sec\n", Console::FG_YELLOW);

                sleep(3);

            } catch (\Exception $e) {
                $this->stdout("error send order {$order->id}\n", Console::FG_RED);
                var_dump($e->getMessage());
            }
        }

    }

    /**
     * @param ShopOrder $order
     * @return bool
     */
    protected function sendToEmailAdmin(ShopOrder $order)
    {

        if ($order->counter_send_queue > 1) {
            return false;
        }

        try {

            if (\Yii::$app->shop->notifyEmails) {
                foreach (\Yii::$app->shop->notifyEmails as $email) {

                    \Yii::$app->mailer->htmlLayout = false;
                    \Yii::$app->mailer->textLayout = false;

                    \Yii::$app->mailer->compose('@mail/modules/shop/admin_create_order_mini', [
                        'order' => $order
                    ])
                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                        ->setTo($email)
                        ->setSubject('Заказ на новом сайте!' . ' №' . $order->id)
                        ->send();
                }
            }

            $this->stdout("email sent done\n", Console::FG_GREEN);

        } catch (\Exception $e) {
            \Yii::error('Ошибка отправки email: ' . $e->getMessage());

            var_dump($e->getMessage());
            var_dump($e->getFile());
            var_dump($e->getLine());

            $this->stdout("email sent failed\n", Console::FG_RED);
        }
    }

    /**
     * @param ShopOrder $order
     * @param string $campaignToken
     * @return bool
     */
    protected function sendToGetresponseSuperVizer(ShopOrder $order, $campaignToken = 'N')
    {

        return;
        try {
            $grClient = \Yii::$app->getResponseService;
            $grClient->setCampaignToken($campaignToken);
            $campaign = $grClient->getCampaigns()->getCampaign($grClient->getCampaignToken());

            $newLetter = new \common\components\email\services\modules\newsLetters\GRCreateNewsLettersOptions([
                'name' => 'Заказ на новом сайте!' . ' №' . $order->id,
                'type' => 'broadcast', // draft - черновик broadcast - рассылка
                'editor' => 'html2',
                'subject' => 'Заказ на новом сайте!' . ' №' . $order->id, //*
                'campaign' => [
                    'campaignId' => $campaign['campaignId'],
                ], //*
                'fromField' => [
                    'fromFieldId' => $campaign['confirmation']['fromField']['fromFieldId']
                ], //*

                'replyTo' => null,
                'content' => [
                    'html' => $this->renderPartial('@mail/modules/shop/admin_create_order_mini', [
                        'order' => $order
                    ])
                ], //*

                'flags' => [], //Message flags. Allowed values: openrate, clicktrack and google_analytics
//            'attachments' => 'test',

                'sendSettings' => [
                    'selectedCampaigns' => [
                        $campaign['campaignId']
                    ],
                    /*'selectedContacts' => [
                        $campaign['campaignId']
                    ],*/
                    'timeTravel' => 'false',
                    'perfectTiming' => 'false',
                ], //*
            ]);

            $createNewsLetters = $grClient->getNewsLetters()->sendNewsletter($newLetter);

            if (is_array($createNewsLetters) && array_key_exists('error', $createNewsLetters)) {
                throw new \Exception(print_r($createNewsLetters, true));
            }
            $this->stdout("Response letter id: {$createNewsLetters['newsletterId']}\n", Console::FG_GREEN);

        } catch (\Exception $e) {
            \Yii::error('Ошибка отправки в getresponse: ' . $e->getMessage());

            var_dump($e->getMessage());
            var_dump($e->getFile());
            var_dump($e->getLine());

            $this->stdout("Response sent failed\n", Console::FG_RED);
        }
    }

    /**
     * @param ShopOrder $order
     * @param string $campaignToken
     * @return bool
     */
    protected function sendToGetresponseSubscribe(ShopOrder $order, $campaignToken = '9')
    {

        if ($order->counter_send_queue > 1) {
            return false;
        }

        try {
            $grClient = \Yii::$app->getResponseService;
            $grClient->setCampaignToken($campaignToken);
            $campaign = $grClient->getCampaigns()->getCampaign($grClient->getCampaignToken());

            $contact = new \rvkulikov\yii2\getResponse\modules\contacts\GRCreateContactOptions([
                "name" => $order->user->name,
                "email" => $order->user->email,
                "dayOfCycle" => null,
                "ipAddress" => null,
                'campaign' => [
                    'campaignId' => $campaign['campaignId'],
                ],
            ]);

            $createContacts = $grClient->getContacts()->createContact($contact);

            if (is_array($createContacts) && array_key_exists('error', $createContacts)) {
                throw new \Exception(print_r($createContacts, true));
            }
            $this->stdout(" Email {$order->user->email} subscribed \n", Console::FG_GREEN);

        } catch (\Exception $e) {
            // There is another resource with the same value of unique property
            // @see https://apidocs.getresponse.com/en/v3/errors/1008
            // You tried to add contact that is already on your blacklist
            // @see https://apidocs.getresponse.com/v3/errors/1002
            if ($e->getCode() == 1008 || $e->getCode() == 1002) {
                $this->stdout(" Email {$order->user->email} subscribed \n", Console::FG_GREEN);
            } else {
                \Yii::error('Ошибка отправки в getresponse: ' . $e->getMessage());

                var_dump($e->getMessage());
                var_dump($e->getFile());
                var_dump($e->getLine());
                var_dump($contact);

                $this->stdout("Response sent failed\n", Console::FG_RED);
            }
        }
    }


    protected function getOrders()
    {
        return ShopOrder::find()
            ->innerJoinWith(['user'])
            ->andWhere(['shop_order.status_code' => [ShopOrderStatus::STATUS_WAIT_PAY, ShopOrderStatus::STATUS_SEND_QUEUE]])
            ->andWhere('shop_order.id > 14564')
            ->andWhere('shop_order.user_id NOT IN (1000, 36574, 68471, 1267)')// TODO test user
            /*            ->andWhere('shop_order.counter_error_queue <= :counter_error_queue', [
                            ':counter_error_queue' => self::COUNT_MAX_ERROR_QUEUE
                        ])*/
            /*            ->andWhere('shop_order.counter_send_queue <= :counter_send_queue', [
                            ':counter_send_queue' => self::COUNT_MAX_ERROR_QUEUE
                        ])*/
            ->orderBy('shop_order.id DESC')
            ->all();

    }

    protected function getNewOrders()
    {
        return \Yii::$app->db->useMaster(function () {
            return ShopOrder::find()
                ->innerJoinWith(['user'])
                ->andWhere(['OR',
                    ['AND',
                        ['shop_order.status_code' => [ShopOrderStatus::STATUS_WAIT_PAY, ShopOrderStatus::STATUS_SEND_QUEUE]],
                        'shop_order.created_at < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 minute))'
                    ],
                    ['AND',
                        ['shop_order.status_code' => ShopOrderStatus::STATUS_DELAYED],
                        'shop_order.created_at < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 11 minute))'
                    ],
                ])
                ->andWhere('shop_order.id > 258698')
                ->andWhere('shop_order.user_id NOT IN (1000, 36574, 68471, 1267)')// TODO test user
                // задержка в 1 мин перед отправкой (чтобы клиент указал на finishpage доп.данные)

                /*            ->andWhere('shop_order.counter_error_queue <= :counter_error_queue', [
                                ':counter_error_queue' => self::COUNT_MAX_ERROR_QUEUE
                            ])*/
                ->andWhere('shop_order.counter_send_queue < 1')
                ->andWhere('shop_order.pay_system_id != '.\Yii::$app->kfssApiV2::PAY_SYSTEM_ID_CARD) //Заказы с онлйн оплатой ходят через АПИ, а не через очереди
                ->orderBy('shop_order.id DESC')
                ->all();
        });

//        return ShopOrder::find()
//            ->innerJoinWith(['user'])
//            ->andWhere(['OR',
//                ['AND',
//                    ['shop_order.status_code' => [ShopOrderStatus::STATUS_WAIT_PAY, ShopOrderStatus::STATUS_SEND_QUEUE]],
//                    'shop_order.created_at < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 minute))'
//                ],
//                ['AND',
//                    ['shop_order.status_code' => ShopOrderStatus::STATUS_DELAYED],
//                    'shop_order.created_at < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 11 minute))'
//                ],
//            ])
//            ->andWhere('shop_order.id > 124749')
//            ->andWhere('shop_order.user_id NOT IN (1000, 36574, 68471, 1267)')// TODO test user
//            // задержка в 1 мин перед отправкой (чтобы клиент указал на finishpage доп.данные)
//
//            /*            ->andWhere('shop_order.counter_error_queue <= :counter_error_queue', [
//                            ':counter_error_queue' => self::COUNT_MAX_ERROR_QUEUE
//                        ])*/
//            /*            ->andWhere('shop_order.counter_send_queue <= :counter_send_queue', [
//                            ':counter_send_queue' => self::COUNT_MAX_ERROR_QUEUE
//                        ])*/
//            ->orderBy('shop_order.id DESC')
//            ->all();

    }

    /**
     * sql для получения брошенных корзин
     * @return string
     */
    protected function getAbandonedBasketSql()
    {
        $sql = <<<SQL
SELECT * FROM (
    SELECT 
      COALESCE(user.phone, fuser.phone) AS PROP_PHONE, 
      GROUP_CONCAT(CONCAT(products.name, ' [', COALESCE(lot_num.value, cce.bitrix_id), ']') SEPARATOR ', ') AS PROP_EXTERNAL_ORDER_PRODUCTS, 
      GROUP_CONCAT(products.id SEPARATOR ', ') AS BASKETS
    FROM (
        SELECT sb.fuser_id
        FROM `shop_basket` AS sb 
        WHERE sb.order_id IS NULL 
          AND sb.created_at >= UNIX_TIMESTAMP(NOW() - INTERVAL 60 MINUTE)
          AND sb.has_removed = 0
          -- нет товаров, добавленных за последние 10 минут
          AND NOT EXISTS (
            SELECT 1 
            FROM shop_basket sb2 
            WHERE sb2.fuser_id = sb.fuser_id 
              AND sb2.order_id IS NULL 
              AND sb2.has_removed = 0 
              AND sb2.created_at >= UNIX_TIMESTAMP(NOW() - INTERVAL 30 MINUTE)
          )
        GROUP BY sb.fuser_id
        ORDER BY sb.created_at DESC
        LIMIT 20
    ) AS sb 
    LEFT JOIN shop_fuser AS fuser ON fuser.id = sb.fuser_id
    LEFT JOIN shop_basket AS products ON sb.fuser_id = products.fuser_id AND products.has_removed = 0
    LEFT JOIN cms_content_element AS cce ON cce.id = products.main_product_id
    LEFT JOIN cms_content_element_property AS lot_num ON lot_num.element_id = cce.id AND property_id = (SELECT id FROM cms_content_property WHERE code = 'LOT_NUM')
    LEFT JOIN cms_user AS user ON user.id = fuser.user_id
    WHERE (fuser.phone IS NOT NULL OR user.phone IS NOT NULL)
    GROUP BY sb.fuser_id
) p
WHERE NOT EXISTS (SELECT 1 FROM ss_preorders_logs ss WHERE ss.phone = p.PROP_PHONE AND ss.created_at > NOW() - INTERVAL 30 MINUTE )
SQL;

        return $sql;
    }

    /**
     *  Отправка брошенных корзин через АПИ КФСС
     *
     * @param bool $fuserId - по сути для тестов. Отправка брошенок определенного фузера
     */
    public function actionAbandonedViaKfssApi($userId = false)
    {
        $this->stdout("Export abandoned carts" . PHP_EOL);

        /** @var ShopFuserQuery $abandonedFusersQuery */
        $abandonedFusersQuery = ShopFuser::getAbandonedBasketsKfssQuery();

        if ($userId){
            $this->stdout(" -- added User filter [UserId={$userId}]" . PHP_EOL);
            $abandonedFusersQuery->andWhere(['shop_fuser.user_id' => $userId]);
        }

        $abandonedFusers = $abandonedFusersQuery->all();

        if ($abandonedFusers){
            $this->stdout("Found abandoned carts num = " . count($abandonedFusers) . PHP_EOL);

            /** @var ShopFuser $abandonedFuser */
            foreach ($abandonedFusers as $abandonedFuser) {
                $response = \Yii::$app->kfssApiV2->sendAbandonedCart($abandonedFuser->external_order_id);
                $msg = "Export abandoned cart orderNumber='{$abandonedFuser->external_order_id}' (fuserId='{$abandonedFuser->id}'). Result=" . ($response === true ? 'SUCCESS' : 'FAIL');

                if ($response === true){
                    $abandonedFuser->external_order_id = '';
                    $abandonedFuser->save(false);
                }

                $this->stdout($msg . PHP_EOL, Console::FG_YELLOW);
                \Yii::error(
                    $msg,
                    __METHOD__
                );
            }
        }else{
            $this->stdout("No abandoned carts" . PHP_EOL);
        }

        $this->stdout("Done" . PHP_EOL);

        return;
    }

}