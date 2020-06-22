<?php

namespace modules\shopandshow\models\newEntities\shop;

use console\controllers\queues\jobs\Job;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopOrderStatus;
use modules\shopandshow\services\Survey;

/*
5E7C4565A2F0EE82E0538201090A6A04 Отменен Заказ отменен
5E7BF7E8C7CED4A8E0538201090A2AA2 Создается Заказ находится в процессе ввода
5E7BB735E592C108E0538201090ADABA Проверен Заказ прошел проверку
5E7BA91651511219E0538201090ACBAD Исправлен после проверки Заказ был модифицирован после проверки
5E7BA91651521219E0538201090ACBAD Ждет предоплаты Заказ сформирован и ждет предоплаты
5E7BB735E594C108E0538201090ADABA Готов к формированию отправлений Заказ готов к формированию отправлений
5E7BA91651531219E0538201090ACBAD Частично готовы отправления Часть товара для заказа включена в отправления
5E7BA91651541219E0538201090ACBAD Нехватка товара после проверки При проверке возникла нехватка товара
5E7C4565A2F3EE82E0538201090A6A04 Не прошел проверку Возникла ошибка при проверке
5E7BA91651551219E0538201090ACBAD Полностью готовы отправления Весть товар для заказа включен в отправления
5E7BA91651561219E0538201090ACBAD Отправлен - ждет оплаты Клиенту отправлены счета - ждем оплаты
5E7BA91651571219E0538201090ACBAD Частично оплачен Поступила оплата не за весь заказ
5E7BA91651581219E0538201090ACBAD Полностью оплачен Поступила оплата за весь заказ
5E7BA91651591219E0538201090ACBAD Оплачен и отправлен За заказ полностью получена оплата и он отправлен клиенту
5E7BA916515A1219E0538201090ACBAD Частично отправлен Для заказа есть отправленные посылки
5E7BA916515B1219E0538201090ACBAD Недостаточно средств для отправки Для отправки заказа недостаточно средств на лицевом счете
5E7BA916515C1219E0538201090ACBAD Есть ошибочные посылки Обнаружены посылки с неверной тарификацией
5E7BA916515D1219E0538201090ACBAD Нехватка товара на производстве Для части посылок не найден товар
6B547CC9B4804C35E0538201090A9591 Передается на производство Данные по заказу отправлены на производство, но ответ еще не получен
5E7BA916515E1219E0538201090ACBAD На производстве Посылки для заказа находятся на производстве
5E7BA916515F1219E0538201090ACBAD Повтор после возврата Повторное формирование РПО после возврата
5E7BA91651601219E0538201090ACBAD Не существует Псевдо-статус для отработки бизнес-скриптов при создании
5E7BA91651611219E0538201090ACBAD Дополнительная проверка Статус для дополнительной проверки заказа
*/

class OrderStatus extends ShopOrderStatus
{

    public $statusMap = [
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

    //Мапинг статусов в контексте с причинами смены статусов
    //STATUS_GUID -> REASON_GUID -> STATUS FOR SITE
    public $reasonToStatusMap = [
        '5E7C4565A2F0EE82E0538201090A6A04'  => [ // Отменен Заказ отменен 0
            '82552A7509FBE4F9E0538201090AFB96'  => ShopOrderStatus::STATUS_FAKE, //Фэйковый статус заказа
        ],
    ];

    public $order_guid;
    public $status_guid;
    public $channel_guid;
    public $reason_guid;

    public function addData()
    {
        if (!$this->order_guid) {
            Job::dump('guid empty');

            return false;
        }
        Job::dump('OrderGuid: ' . $this->order_guid);


        $siteSaleChanel = false;
        switch ($this->channel_guid) {
            case '6A3032E0EF04D151E0538201090A2BC3': // САЙТ Shop & Show
            case '5D9CECF18C301919E0538201090A492C': // 88003016010
            case '5D9CECF18C291919E0538201090A492C': // 88007755665 соц. сети
            case '81C98E92CC656F0CE0538201090A37B4': // CPA
                $siteSaleChanel = true;
                break;
        }

        if (!$siteSaleChanel) {
            return true;
        }

        /** @var ShopOrder $shopOrder */
        if (!$shopOrder = Guids::getEntityByGuid($this->order_guid)) {
            Job::dump('Order not found by guid: ' . $this->order_guid);

            return true;
        }

        // если статус неизвестен
        if (!array_key_exists($this->status_guid, $this->statusMap)) {
            // и заказ в стадии "отправлен в очередь", считаем что заказ принят
            if ($shopOrder->status_code == ShopOrderStatus::STATUS_SEND_QUEUE || $shopOrder->status_code == ShopOrderStatus::STATUS_WAIT_PAY) {
                $shopOrder->setStatus(ShopOrderStatus::STATUS_SUCCESS);
            } // просто какой-то статус, который нам не интересен
            else {
                return true;
            }
        } else {
            //Ставим обновленный статус
            //Предварительно проверяем так же маппинг причин смены статуса
            if ($this->reason_guid && !empty($this->reasonToStatusMap[$this->status_guid][$this->reason_guid])){
                $status = $this->reasonToStatusMap[$this->status_guid][$this->reason_guid];
            }else{
                $status = $this->statusMap[$this->status_guid];
            }

            $shopOrder->setStatus($status);
        }

        if ($shopOrder->isAttributeChanged('status_code')) {
            if (!$shopOrder->validate(['status_code'])) {
                Job::dump(' failed to validate status ' . print_r($shopOrder->getErrors(), true));
                return false;
                //            throw new Exception("Order model data not valid: " . json_encode($shopOrder->getErrors()));
            }

            if ($shopOrder->status_code == ShopOrderStatus::STATUS_CANCELED) {
                $shopOrder->reason_canceled = $this->reason_guid;
            }

            if (!$shopOrder->save(false, ['status_code', 'reason_canceled'])) {
                Job::dump(' failed to save status ' . print_r($shopOrder->getErrors(), true));
                return false;
            }
            Job::dump(' Success to save status ' . $shopOrder->status_code);


            // шлем письма с опросником
            if ($shopOrder->status_code == ShopOrderStatus::STATUS_COMPLETED) {
                Survey::sendSurvey(Survey::ORDER_COMPLETE_TYPE, $shopOrder);
            } elseif ($shopOrder->status_code == ShopOrderStatus::STATUS_CANCELED) {
                if (time() - $shopOrder->created_at < DAYS_2) {
                    Survey::sendSurvey(Survey::ORDER_CANCEL_TYPE, $shopOrder);
                }
            }
        }

        return true;
    }
}