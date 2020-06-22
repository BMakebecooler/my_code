<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 15/03/2019
 * Time: 16:29
 */

namespace common\models;


class ShopOrderStatus extends \common\models\generated\models\ShopOrderStatus
{
    const STATUS_CODE_START  = "N";
    const STATUS_CODE_END    = "F";

    /**
     * Выгружен во фронт
     */
    const STATUS_SEND_QUEUE = 'Q';

    /**
     * Заказ пришел в удаленную систему
     */
    const STATUS_SUCCESS = 'B';

    /**
     * Отправка заказа завершилась неудачей
     */
    const STATUS_FAILED = 'P';

    /**
     * Принят, ожидается оплата (STATUS_CODE_START)
     */
    const STATUS_WAIT_PAY = 'N';

    /**
     * Отменен
     */
    const STATUS_CANCELED = 'C';

    /**
     * Выполнен (STATUS_CODE_END)
     */
    const STATUS_COMPLETED = 'F';

    /**
     * Проверен, все хорошо
     */
    const STATUS_CHECKED = 'G';

    /**
     * Готов к отправке
     */
    const STATUS_READY = 'R';

    /**
     * Отправлен, едет
     */
    const STATUS_TRAVEL = 'T';

    /**
     * Еще формируется клиентом
     */
    const STATUS_DELAYED = 'D';

    /**
     * Статус "не настоящий"
     */
    const STATUS_FAKE = 'Z';

    public function getDisplayName()
    {
        switch ($this->code) {
            case self::STATUS_WAIT_PAY:
            case self::STATUS_SEND_QUEUE:
            case self::STATUS_SUCCESS:
            case self::STATUS_DELAYED:
                return 'Ожидается звонок от оператора';
            case self::STATUS_CHECKED:
                return 'Заказ принят';
            case self::STATUS_READY:
                return 'Готов к отправке';
            case self::STATUS_TRAVEL:
                return 'Отправлен';
            case self::STATUS_CANCELED:
                return 'Заказ отменен';
            case self::STATUS_COMPLETED:
                return 'Заказ выполнен';
            case self::STATUS_FAKE:
                return 'FAKE';
        }
    }
}