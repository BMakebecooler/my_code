<?php

/**
 * php ./yii alerts/products
 */

namespace console\controllers\alerts;

use common\components\sms\Sms;
use console\controllers\export\ExportController;

/**
 * Class ProductsController
 * @package console\controllers
 */
class ProductsController extends ExportController
{

    const CRITICAL_ALERTS_TITLE = 'AHTUNG! ';

    public function actionIndex()
    {
        $hour = (int)date('G');

        /**
         * Алертим только в перид с 7 утра до 10 вечера
         */
        if ($hour >= 7 && $hour <= 22) {
            $this->checkQuantityShareProducts();
        }
    }

    /**
     * Алерт когда на баннерах на главной нет остатков
     * @return bool
     */
    private function checkQuantityShareProducts()
    {

        $sql = <<<SQL
    SELECT ss.id, sp.quantity, COALESCE(lot_num.value, cce.id) AS product_id
    FROM `ss_shares` ss
    INNER JOIN cms_content_element cce ON ss.bitrix_product_id = cce.bitrix_id
    INNER JOIN shop_product sp ON cce.id = sp.id AND sp.quantity <= 10
    LEFT JOIN cms_content_element_property AS lot_num ON lot_num.element_id = cce.id AND property_id = (SELECT id FROM cms_content_property WHERE code = 'LOT_NUM')
    WHERE ss.begin_datetime >= UNIX_TIMESTAMP(FROM_UNIXTIME(UNIX_TIMESTAMP(), '%Y-%m-%d 07:00:00'))  AND ss.end_datetime <= UNIX_TIMESTAMP(FROM_UNIXTIME(UNIX_TIMESTAMP(NOW() + INTERVAL 1 DAY), '%Y-%m-%d 06:59:59'))
    LIMIT 50;
SQL;

        $data = \Yii::$app->db->createCommand($sql)->queryAll();

        if (!$data) {
            return false;
        }

        $text = '';

        foreach ($data as $item) {
            $text .= sprintf('Баннер №%s, лот:%s, осталось: %s ', $item['id'], $item['product_id'], $item['quantity']);
        }

        $this->sendSms($text);
    }

    private function sendSms($text, $phoneList = [
        //'+7(929)580-60-20', //Коваленко
        '+7(926)581-28-70', //Анисимов
        '+7(977)438-80-25', //Селянский
        '+7(965)151-72-18', //Иван Гуторов
        '+7(977)280-84-74', //Сонина
        '+7(916)193-88-32', //Савельева Аня
        '+7(985)213-01-32', //Датнова Анна
    ])
    {
        foreach ($phoneList as $phone) {
            \Yii::$app->sms->sendSms($phone, $text, true, Sms::SMS_TYPE_FOR_ADMINS);
        }
    }


}
