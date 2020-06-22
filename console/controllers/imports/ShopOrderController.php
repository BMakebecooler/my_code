<?php

/**
 * php ./yii imports/shop-order/import
 * php ./yii imports/shop-order/import-orders-statuses
 * php ./yii imports/shop-order/import-orders-statuses-history
 */

namespace console\controllers\imports;

use common\helpers\Developers;
use common\helpers\Msg;
use common\models\user\User;
use modules\shopandshow\models\common\Guid;
use modules\shopandshow\models\shop\ShopBuyer;
use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopOrderChange;
use modules\shopandshow\models\shop\ShopOrderStatus;
use modules\shopandshow\models\users\UserEmail;
use yii\base\Exception;
use yii\helpers\Console;

/**
 * Class ShopOrderController
 * @package console\controllers
 */
class ShopOrderController extends \yii\console\Controller
{
    /** @var datetime минимальная дата создания заказа для синхронизации */
    protected $date_begin;

    public function init()
    {
        parent::init();

        $this->date_begin = '2018-01-01 00:00:00';
    }


    public function actionImport()
    {
        if ($this->importNewOrders()) {
            $this->stdout("Заказы импортированы!\n", Console::FG_GREEN);
        } else {
            $this->stdout("Заказы не импортированы!\n", Console::FG_RED);
        }

        $this->stdout("Импорт заказов закончен!\n", Console::FG_YELLOW);
    }

    /**
     * @return bool
     */
    protected function importNewOrders()
    {
        $sql = <<<SQL
        SELECT 
          o.id, o.person_type_id, o.canceled, o.status_id, o.price_delivery, o.price, o.discount_value, o.user_id, o.date_insert, o.date_status, o.tax_value,  
          og.guid order_guid, ug.guid user_guid, o_source.value source, o_phone.value our_phone
        FROM front2.b_sale_order as o
        INNER JOIN front2.sands_guid_storage og ON og.entity = 'ORDER' and og.local_id = o.id
        LEFT JOIN front2.sands_guid_storage ug ON ug.entity = 'USER' and ug.local_id = o.user_id
        LEFT JOIN ss_guids sg ON sg.guid = og.guid
        LEFT JOIN shop_order so ON so.guid_id = sg.id
        LEFT JOIN front2.b_sale_order_props_value as o_source 
                ON o.id = o_source.order_id and o_source.ORDER_PROPS_ID = 12
        LEFT JOIN front2.b_sale_order_props_value as o_phone 
                ON o.id = o_phone.order_id and o_phone.ORDER_PROPS_ID = 16
                        
        WHERE o.date_insert >= :date_begin
          AND o.date_insert < NOW() - INTERVAL 10 MINUTE
          AND o_phone.value in ('88007752250', '88003016010')
          AND so.id IS NULL;
SQL;
        $bitrixOrders = \Yii::$app->db->createCommand($sql, [
            ':date_begin' => $this->date_begin
        ])->queryAll();

        foreach ($bitrixOrders as $bitrixOrder) {
            if (!$this->createOrder($bitrixOrder)) {
                //return false;
                continue;
            }
        }

        return true;
    }

    protected function createOrder(array $bitrixOrder)
    {
        /** @var User $user */
        $user = $this->getOrCreateUserByOrder($bitrixOrder);
        if (!$user) {
            Console::stdout('Failed to create user');
            \Yii::error('Failed to create user');
            return false;
        }

        $shopBuyer = ShopBuyer::findOne(['cms_user_id' => $user->id]);
        if (!$shopBuyer) {
            $shopBuyer = new ShopBuyer([
                'shop_person_type_id' => (int)$bitrixOrder['person_type_id'],
                'cms_user_id' => $user->id
            ]);

            if (!$shopBuyer->save()) {
                Console::stdout(print_r($shopBuyer->getErrors(), true));
                Developers::reportProblem(
                    'Ошибка при создании байера из битрикса' . PHP_EOL .
                    print_r($shopBuyer->getErrors(), true) . PHP_EOL .
                    print_r($shopBuyer->attributes, true)
                );
                \Yii::error('Ошибка при создании байера из битрикса');
                return false;
            }
        }

        $shopFuser = new \modules\shopandshow\models\shop\ShopFuser([
            'person_type_id' => (int)$bitrixOrder['person_type_id'],
            'buyer_id' => $shopBuyer->id,
            'user_id' => $user->id
        ]);
        $shopFuser->loadDefaultValues();

        try {
            $order = ShopOrder::createByFuser($shopFuser);
            $order->guid->setGuid($bitrixOrder['order_guid']);
        } catch (Exception $e) {
            \Yii::error('Ошибка при создании заказа из битрикса 1 ' . $e->getMessage());
            Console::stdout('Failed to create order');
            return false;
        }


        // источник заказа
        $order->source = ShopOrder::SOURCE_BITRIX;
        $order->source_detail = $bitrixOrder['source'] == 'NEW_SITE'
            ? ShopOrder::SOURCE_DETAIL_SITE
            : ($bitrixOrder['our_phone'] == '88007752250'
                ? ShopOrder::SOURCE_DETAIL_PHONE1
                : ShopOrder::SOURCE_DETAIL_PHONE2
            );

        $order->setStatus(ShopOrderStatus::STATUS_SUCCESS);
        $order->created_by = $user->id;
        $order->created_at = strtotime($bitrixOrder['date_insert']);
        $order->updated_at = strtotime($bitrixOrder['date_status']);
        $order->status_at = strtotime($bitrixOrder['date_status']);

        $order->price = $bitrixOrder['price'];
        $order->price_delivery = $bitrixOrder['price_delivery'];
        $order->discount_value = $bitrixOrder['discount_value'];

        $order->bitrix_id = $bitrixOrder['id'];
        $order->order_number = $bitrixOrder['id'];

        if (!$order->save()) {
            Console::stdout(print_r($order->getErrors(), true));
            Developers::reportProblem(
                'Ошибка при создании заказа из битрикса' . PHP_EOL .
                print_r($order->getErrors(), true) . PHP_EOL .
                print_r($order->attributes, true)
            );
            \Yii::error('Ошибка при создании заказа из битрикса 2' . print_r($order->getErrors(), true));
            return false;
        }

        if (!$this->createOrderPositions($order)) {
            return false;
        }

        return true;
    }

    protected function getOrCreateUserByOrder(array $bitrixOrder)
    {
        /** @var User $user */
        $user = null;
        if ($bitrixOrder['user_guid']) {
            $this->stdout("searching user by guid [{$bitrixOrder['user_guid']}]: ", Console::FG_YELLOW);
            $user = \modules\shopandshow\lists\Guids::getEntityByGuid($bitrixOrder['user_guid']);
            if ($user) {
                $this->stdout("{$user->displayName} [{$user->id}]\n", Console::FG_GREEN);
            } else {
                $this->stdout("not found\n", Console::FG_YELLOW);
            }

        }

        if (!$user) {
            $this->stdout("searching user by bitrix_id [{$bitrixOrder['user_id']}]: ", Console::FG_YELLOW);
            $bitrixUser = \common\lists\Bitrix::getUserById($bitrixOrder['user_id']);
            if (!$bitrixUser) {
                $this->stdout("not found\n", Console::FG_YELLOW);
                \Yii::error('User not found in bitrix by id: ' . $bitrixOrder['user_id']);
                return false;
            } else {
                $this->stdout("{$bitrixUser['LOGIN']}\n", Console::FG_GREEN);
            }

            $user = User::findOne(['username' => $bitrixUser['LOGIN']]);
            if ($user) {
                $this->stdout("found user {$user->displayName} [{$user->id}]\n", Console::FG_GREEN);
                return $user;
            }

            if (!\Yii::$app->has('user', false)) {
                \Yii::$app->set('user', [
                    'class' => 'yii\web\User',
                    'identityClass' => 'common\models\user\User'
                ]);
            }

            $model = new \common\models\user\authorizations\SignupForm();
            $model->setScenario(User::SCENARIO_RIGISTRATION_FROM_BITRIX);

            // ожидаем +0 000 000-00-00
            if (!preg_match('/^\+\d\s\d{3}\s\d{3}\-\d{2}\-\d{2}$/', trim($bitrixUser['PERSONAL_PHONE']))) {
                // режем все кроме цифр
                $bitrixUser['PERSONAL_PHONE'] = preg_replace('/[^0-9]/', '', $bitrixUser['PERSONAL_PHONE']);
                // разбиваем на токены
                if (preg_match('/^(\d)(\d{3})(\d{3})(\d{2})(\d{2})$/', $bitrixUser['PERSONAL_PHONE'], $match)) {
                    // собираем по формату
                    $bitrixUser['PERSONAL_PHONE'] = sprintf('+%d %03d %03d-%02d-%02d', $match[1] == 8 ? 7 : $match[1], $match[2], $match[3], $match[4], $match[5]);
                } else {
                    $bitrixUser['PERSONAL_PHONE'] = '';
                }
            }

            if (empty($bitrixUser['PERSONAL_PHONE'])) {
                $bitrixUser['PERSONAL_PHONE'] = '+7 800 301-60-10';
            }

            if (!filter_var(mb_strtolower($bitrixUser['EMAIL']), FILTER_VALIDATE_EMAIL)) {
                $bitrixUser['EMAIL'] = '';
            }

            $name = !empty(trim($bitrixUser['NAME'])) ? $bitrixUser['NAME'] : $bitrixUser['LOGIN'];
            $model->setAttributes([
                'email' => $bitrixUser['EMAIL'],
                'username' => $bitrixUser['LOGIN'],
                'password' => 'password',
                'name' => $name,
                'surname' => $bitrixUser['LAST_NAME'],
                'patronymic' => $bitrixUser['SECOND_NAME'],
                'isSubscribe' => true,
                'phone' => $bitrixUser['PERSONAL_PHONE'],
                'bitrix_id' => $bitrixUser['ID'],
                'guid' => $bitrixUser['guid'],
                //так как импортируются только заказы с тел сайта (как оказалось не только, ибо тел проставляется и заказам не с тел)
                //Но так как пользователи не с тел уже были созданы, то так тоже корректно
                'source' => UserEmail::SOURCE_SITE_PHONE,
                'source_detail' => UserEmail::SOURCE_DETAIL_CHECK_ORDER,
            ], false);

            $user = $model->signup();
            if (!$user) {
                Console::stdout($name);
                Console::stdout(print_r($model->getErrors(), true));
                Console::stdout(print_r($model->attributes, true));
                Console::stdout(print_r($bitrixUser, true));

                Developers::reportProblem(
                    'Не удалось создать пользователя при импорте заказа из битрикса' . PHP_EOL .
                    print_r($model->getErrors(), true) . PHP_EOL .
                    print_r($model->attributes, true) . PHP_EOL .
                    print_r($bitrixUser, true)
                );
                \Yii::error('Не удалось создать пользователя при импорте заказа из битрикса');

                return false;
            }
        }

        return $user;
    }

    protected function createOrderPositions(ShopOrder $order)
    {
        $bitrixPositions = \common\lists\Bitrix::getOrderPositionsByOrderGuid($order->guid->getGuid());
        foreach ($bitrixPositions as $position) {
            // если указан 6-й iblock, то это ювелирка, реальный id модификации лежит в PRODUCT_XML_ID
            $productId = $position['IBLOCK_ID'] == 6 ? $position['PRODUCT_XML_ID'] : $position['PRODUCT_ID'];
            $product = \common\lists\Contents::getContentElementByBitrixId($productId, [PRODUCT_CONTENT_ID, OFFERS_CONTENT_ID]);
            if (!$product) {
                Console::stdout('product not found by bitrix_id ' . $productId);

                /*Developers::reportProblem(
                    'Не найден продукт при импорте заказа из битрикса ' . $productId
                );*/
                \Yii::error('Не найден продукт при импорте заказа из битрикса ' . $productId);

                // todo: для работы инструмента мониторинг дня наличие товаров в заказе не обязательно
                continue;
            }

            $basket = new \modules\shopandshow\models\shop\ShopBasket();
            $basket->setAttributes([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $position['QUANTITY'] < 1 ? 1 : $position['QUANTITY'],
                'name' => $position['NAME'],
                'price' => $position['PRICE'],
                'currency_code' => 'RUB',
                'site_id' => $order->site_id,
                'discount_price' => $position['DISCOUNT_PRICE'],
                'has_removed' => 0,
                'discount_name' => $position['DISCOUNT_NAME'],
                'discount_value' => $position['DISCOUNT_VALUE'],
                'main_product_id' => $product->product ? $product->product->id : $product->id
            ]);

            if (!$basket->save()) {
                Console::stdout(print_r($basket->getErrors(), true));

                Developers::reportProblem(
                    'Не удалось сохранить позицию заказа при импорте заказа из битрикса' . PHP_EOL .
                    print_r($basket->getErrors(), true) . PHP_EOL .
                    Print_r($basket->attributes, true)
                );
                \Yii::error('Не удалось сохранить позицию заказа при импорте заказа из битрикса');

                return false;
            }
        }

        return true;
    }

    public function actionImportOrdersStatuses()
    {

        $statusCode = '';
        $orderGuid = '';
        $statusesLog = [];

        $shopOrders = ShopOrder::tableName();
        $guids = Guid::tableName();

        $sql = <<<SQL
UPDATE IGNORE
  {$shopOrders} 
SET
  status_code = :status_code
WHERE
  guid_id = (
    SELECT id FROM {$guids} WHERE guid = :order_guid
  ) 
SQL;

        $command = \Yii::$app->db->createCommand($sql)
            ->bindParam(':status_code', $statusCode)
            ->bindParam(':order_guid', $orderGuid);

        $this->stdout("Импорт статусов заказов." . PHP_EOL);

        //Лог в отдельном пхп-файле в массиве (ибо много строк)
        require_once __DIR__ . "/files/orders_statuses_log.php";

        if ($statusesLog) {
            $count = count($statusesLog);
            $this->stdout("Попытка обновить заказов: {$count}" . PHP_EOL);

            Console::startProgress(0, $count);

            $counter = 0;
            $ordersUpdated = 0;
            foreach ($statusesLog as $logItem) {
                $counter++;
                Console::updateProgress($counter, $count);

                list($orderGuid, $statusCode) = $logItem;

                $updated = $command->execute();

                if ($updated) {
                    $ordersUpdated++;
                }
            }

            $this->stdout("Обнолено записей - {$ordersUpdated}" . PHP_EOL, Console::FG_GREEN);
        } else {
            $this->stdout("Нет данных для обновления" . PHP_EOL, Console::FG_RED);
        }

        $this->stdout("Done" . PHP_EOL, Console::FG_GREEN);

        return;
    }

    /**
     * Импорт исторической хронологии смены статусов заказов
     */
    public function actionImportOrdersStatusesHistory()
    {

        $statusMap = [
            '5E7C4565A2F0EE82E0538201090A6A04' => ShopOrderStatus::STATUS_CANCELED, // Отменен Заказ отменен 0
            '6D7EF31FC0ADF100E0538201090A3BB8' => ShopOrderStatus::STATUS_CANCELED, // Есть возврат По данному заказу есть возврат товара 24
            '6D82A5F81C42614EE0538201090A354F' => ShopOrderStatus::STATUS_COMPLETED, // Вручен клиенту Заказ вручен получателю, но оплата еще не поступила 23

            '5E7BB735E592C108E0538201090ADABA' => ShopOrderStatus::STATUS_CHECKED, // Проверен Заказ прошел проверку 2
            '5E7BB735E594C108E0538201090ADABA' => ShopOrderStatus::STATUS_READY, // Готов к формированию отправлений Заказ готов к формированию отправлений 5
            '5E7BA91651561219E0538201090ACBAD' => ShopOrderStatus::STATUS_TRAVEL, // Отправлен - ждет оплаты Клиенту отправлены посылки - ждем оплаты 10
            '5E7BA91651591219E0538201090ACBAD' => ShopOrderStatus::STATUS_TRAVEL, // Оплачен и отправлен За заказ полностью получена оплата и он отправлен клиенту  13

            //Если что то нужно из нижеследующего - назначить букву и раскоментить
//            '5E7BF7E8C7CED4A8E0538201090A2AA2' => '', //Создается Заказ находится в процессе ввода
//            '5E7C4565A2F3EE82E0538201090A6A04' => '', //Не прошел проверку Возникла ошибка при проверке
//            '5E7BA91651611219E0538201090ACBAD' => '', //Дополнительная проверка Статус для дополнительной проверки заказа

//            '5E7BA91651541219E0538201090ACBAD' => '', //Нехватка товара при обеспечении Недостаточно товарных остатков для обеспечения заказа
//            '5E7BA91651551219E0538201090ACBAD' => '', //Полностью готовы отправления Весть товар для заказа включен в отправления
//            '6B547CC9B4804C35E0538201090A9591' => '', //Передается на производство Данные по заказу отправлены на производство, но ответ еще не получен
//            '5E7BA916515E1219E0538201090ACBAD' => '', //На производстве Посылки для заказа находятся на производстве
        ];

        $statusesLog = [];

        $this->stdout("Импорт истории изменения статусов заказов." . PHP_EOL);

        //Лог в отдельном пхп-файле в массиве (ибо много строк)
        //require_once __DIR__ . "/files/orders_statuses_changes_log.php";
        require_once __DIR__ . "/files/orders_statuses_changes_log_2018JULY.php";

        if ($statusesLog) {

            //Изначальное кол-во элементов истории смены статусов
            $countOrigin = count($statusesLog);

            //Кол-во элементов статусов после дедупликации
            $countDeduplicated = 0;

            //* Дедупликация статусов заказов *//
            //Т.к. Из КФСС статусы часто приходят с множественными повторами, приходится их схлопывать до без дублей
            //За основу берем последний статус (из одинаковых), остальные такие же игнорируем (перезаписываем более поздними)

            $statusesLogDeduplicated = [];

            foreach ($statusesLog as $logItem) {

                list($createdAt, $orderGuid, $statusCode) = $logItem;

                if (!$createdAt || !$orderGuid || !$statusCode) {
                    continue;
                }

                if (!isset($statusesLogDeduplicated[$orderGuid])) {
                    $statusesLogDeduplicated[$orderGuid] = [];
                }

                //Если такого статуса для данного заказа еще нет - посчитаем ео как новый
                if (!isset($statusesLogDeduplicated[$orderGuid][$statusCode])) {
                    $countDeduplicated++;
                }

                //Записываем элемент истории (перезаписывая если такой статус уже есть для данного заказа)
                $statusesLogDeduplicated[$orderGuid][$statusCode] = $createdAt;
            }

            $countOrdersNum = count($statusesLogDeduplicated);

            //Теперь из дедуплицированного лога составляем одномерый массив для постоты вставки
            $statusesLog = [];
            foreach ($statusesLogDeduplicated as $orderGuid => $orderStatuses) {
                foreach ($orderStatuses as $statusCode => $statusDate) {
                    $statusesLog[] = [
                        $statusDate,
                        $orderGuid,
                        $statusCode
                    ];
                }
            }

            unset($statusesLogDeduplicated);

            $count = count($statusesLog);
            $counterStep = $count / 50; //каждые 2 процента, сколько это в штуках

            //* /Дедупликация статусов заказов *//

            $this->stdout("Элементов истории заказов в выгрузке: {$countOrigin}" . PHP_EOL);
            $this->stdout("Элементов истории после дедупликации: {$count}" . PHP_EOL);
            $this->stdout("Заказов для которых записываем историю: {$countOrdersNum}" . PHP_EOL);

            if ($statusesLog) {

                $shopOrderId = null;
                $createdAt = '';
                $orderGuid = '';
                $statusChangeDataSerialised = '';
                $statusCode = '';

                $shopOrders = ShopOrder::tableName();
                $shopOrderChange = ShopOrderChange::tableName();
                $guids = Guid::tableName();
                $changeType = ShopOrderChange::ORDER_STATUS_CHANGED;

                //Статус того что заказ пришел в удаленную систему удалять не надо, он системный
                $shopOrderStatusB = ShopOrderStatus::STATUS_SUCCESS;

                $sqlGetShopOrder = <<<SQL
SELECT {$shopOrders}.id
FROM {$guids}
INNER JOIN {$shopOrders} ON {$shopOrders}.guid_id = {$guids}.id
WHERE {$guids}.guid = :order_guid
SQL;

                $commandGetShopOrder = \Yii::$app->db->createCommand($sqlGetShopOrder)
                    ->bindParam(':order_guid', $orderGuid);

                //Удаление старой истории
                $sqlDelStatusHistory = <<<SQL
DELETE FROM {$shopOrderChange}
WHERE
  type = '{$changeType}'
  AND status_code != '{$shopOrderStatusB}'
  AND shop_order_id = :shop_order_id
SQL;

                $commandDel = \Yii::$app->db->createCommand($sqlDelStatusHistory)
                    ->bindParam(':shop_order_id', $shopOrderId);


                $sqlAddStatusElement = <<<SQL
INSERT INTO
  {$shopOrderChange} 
SET
  created_at = :created_at,
  updated_at = :created_at,
  shop_order_id = :shop_order_id,
  type = '{$changeType}',
  data = :status_change_data,
  status_code = :status_code
SQL;

                $commandInsert = \Yii::$app->db->createCommand($sqlAddStatusElement)
                    ->bindParam(':created_at', $createdAt)
                    ->bindParam(':shop_order_id', $shopOrderId)
                    ->bindParam(':status_change_data', $statusChangeDataSerialised)
                    ->bindParam(':status_code', $statusCode);

                //Для логирования списка затронутых заказов.
                $updatedOrders = [];

                Console::startProgress(0, $count);

                $counterGlobal = 0;
                $counter = 0;
                $ordersUpdatedNum = 0;
                foreach ($statusesLog as $logItem) {
                    $counterGlobal++;
                    $counter++;

                    if ($counter >= $counterStep || $counterGlobal == $count) {
                        $counter = 0;
                        Console::updateProgress($counterGlobal, $count);
                    }

                    list($createdAtDate, $orderGuid, $statusCode) = $logItem;

                    $statusName = '';

                    switch ($statusCode) {
                        case ShopOrderStatus::STATUS_WAIT_PAY:
                        case ShopOrderStatus::STATUS_SEND_QUEUE:
                        case ShopOrderStatus::STATUS_SUCCESS:
                        case ShopOrderStatus::STATUS_DELAYED:
                            $statusName = 'Ожидается звонок от оператора';
                            break;
                        case ShopOrderStatus::STATUS_CHECKED:
                            $statusName = 'Заказ принят';
                            break;
                        case ShopOrderStatus::STATUS_READY:
                            $statusName = 'Готов к отправке';
                            break;
                        case ShopOrderStatus::STATUS_TRAVEL:
                            $statusName = 'Отправлен';
                            break;
                        case ShopOrderStatus::STATUS_CANCELED:
                            $statusName = 'Заказ отменен';
                            break;
                        case ShopOrderStatus::STATUS_COMPLETED:
                            $statusName = 'Заказ выполнен';
                            break;
                    }

                    $createdAt = strtotime($createdAtDate);
                    $statusChangeDataSerialised = serialize(['status' => $statusName]);

                    //Прежде чем что то обновлять получим ID заказа и убедимся что он есть

                    $shopOrder = $commandGetShopOrder->queryOne();

                    if ($shopOrder && $shopOrderId = $shopOrder['id']){

                        //Если заказ в обновлении встретился впервые - предварительно удаляем всю имеющуюся историю изменений
                        if (!in_array($orderGuid, $updatedOrders)) {
                            //Добавляем заказ в лог
                            $updatedOrders[] = $orderGuid;

                            //Чистим историю изменения статусов для него
                            $commandDel->execute();
                        }

                        $updated = $commandInsert->execute();

                        if ($updated) {
                            $ordersUpdatedNum++;
                        }

                    }else{
                        //Такого заказа нет - сообщаем об ошибке
                        $errorMsg = "Импорт истории статустов заказов. Не найден заказ с GUID='{$orderGuid}' [Дата: {$createdAtDate} | Статус: {$statusName} ({$statusCode})]";

                        //$this->stdout($errorMsg . PHP_EOL, Console::FG_RED);
                        \Yii::error($errorMsg);
                    }

                }

                $this->stdout("Обнолено записей - {$ordersUpdatedNum}" . PHP_EOL, Console::FG_GREEN);

            } else {
                //Нет данных после дедупликации
                $this->stdout("Нет данных после дедупликации" . PHP_EOL, Console::FG_YELLOW);
            }

        } else {
            $this->stdout("Нет данных для обновления" . PHP_EOL, Console::FG_RED);
        }

        $this->stdout("Done" . PHP_EOL, Console::FG_GREEN);

        return;
    }
}