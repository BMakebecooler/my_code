<?php

/**
 * php ./yii sync/promo/winners
 */

namespace console\controllers\sync\promo;

use common\models\cmsContent\CmsContent;
use common\models\cmsContent\CmsContentElement;
use common\models\cmsContent\CmsContentProperty;
use common\widgets\promo\april2018\PrizeCounter;
use console\controllers\sync\SyncController;
use yii\helpers\Console;

class WinnersController extends SyncController
{

    /** @var array bitrix product_id */
    public $winProducts = [
        4777251 => 'ARK Benefit S504 (004777251)',
        4919527 => 'ARK Benefit S504 (004919527)', // Ювелирный лот
        4777276 => 'Планшет Irbis TZ184 (004777276)',
        4919529 => 'Планшет Irbis TZ184 (004919529)', // Ювелирный лот
        4777280 => 'Телевизор Harper 49U750TS (004777280)',
        4919530 => 'Телевизор Harper 49U750TS (004919530)',  // Ювелирный лот
    ];

    //public $winPeriodStart = '2018-04-12 00:00:00'; // TODO вернуть когда начнется акция
    public $winPeriodStart = '2018-04-23 08:00:00';
    public $winPeriodEnd = '2018-04-29 07:59:00';

    /** @var CmsContent */
    protected $winnerContent;

    /**
     * Старт синхронизации
     */
    public function actionIndex()
    {

        $prizeCounter = new PrizeCounter();

        $this->winPeriodStart = $prizeCounter->getDateStart();
        $this->winPeriodEnd = $prizeCounter->getDateEnd();

        $this->actionSyncWinners();
    }

    /**
     * Синхронизирует победителей
     */
    public function actionSyncWinners()
    {
        $this->winnerContent = CmsContent::findOne(['code' => 'winners']);
        if (!$this->winnerContent) {
            throw new \ErrorException('cms_content с победителями не найден!');
        }

        $winners = $this->getWinners();

        foreach ($winners as $winner) {
            $this->createWinner($winner);
        }

    }

    /**
     * получает список новых победителей из битрикса
     * @return mixed|array
     * @throws \ErrorException
     */
    protected function getWinners()
    {
        $winnerOrderProperty = CmsContentProperty::findOne(['code' => 'winner_order_id', 'content_id' => $this->winnerContent->id]); // 225
        if (!$winnerOrderProperty) {
            throw new \ErrorException('cms_content_property с номером заказа победителя не найден!');
        }

        // немного костыльно, но массив в чистом sql не биндится :-(
        $products = join(',', array_keys($this->winProducts));

        $query = <<<SQL
SELECT 
  bo.id as order_id, 
  IF(be.iblock_id = 6, bb.product_xml_id, bb.product_id) as product_id,
  fio.VALUE AS personal_fio,
  phone.VALUE AS personal_phone,
  city.VALUE AS personal_city,
  bo.DATE_INSERT AS created_at
FROM front2.b_sale_order bo
INNER JOIN front2.b_sale_basket bb ON bb.order_id = bo.id
INNER JOIN front2.b_iblock_element be ON be.id = bb.product_id
LEFT JOIN cms_content_element_property ccep ON ccep.property_id = :property_winner_order_id AND ccep.value = bo.id
LEFT JOIN cms_content_element cce ON cce.id = ccep.element_id
LEFT JOIN front2.b_sale_order_props_value AS city ON city.order_id = bo.id AND city.order_props_id = 32
LEFT JOIN front2.b_sale_order_props_value AS fio ON fio.order_id = bo.id AND fio.order_props_id = 1
LEFT JOIN front2.b_sale_order_props_value AS phone ON phone.order_id = bo.id AND phone.order_props_id = 3
WHERE bo.DATE_INSERT BETWEEN :period_start AND :period_end
  AND IF(be.iblock_id = 6, bb.product_xml_id, bb.product_id) IN ({$products})
  AND cce.id IS NULL
SQL;
        return \Yii::$app->db->createCommand(
            $query,
            [
                ':property_winner_order_id' => $winnerOrderProperty->id,
                ':period_start' => $this->winPeriodStart,
                ':period_end' => $this->winPeriodEnd,
            ]
        )->queryAll();
    }

    /**
     * создает объект победителя в БД
     * @param array $winner - параметры победителя из битрикса
     * @return CmsContentElement
     * @throws \ErrorException
     */
    protected function createWinner($winner)
    {
        $winner_phone = \common\helpers\User::phoneFormat($winner['personal_phone']) ?: $winner['personal_phone'];
        $winner_order_id = $winner['order_id'];
        $winner_product = $this->winProducts[$winner['product_id']] ?? 'Подарок';
        $winner_fio = $this->formatFio($winner['personal_fio']);
        $winner_city = $winner['personal_city'];

        $winnerContentElement = new CmsContentElement();
        $winnerContentElement->content_id = $this->winnerContent->id;
        $winnerContentElement->name = $winner_fio;
        $winnerContentElement->created_at = strtotime($winner['created_at']);

        if (!$winnerContentElement->save()) {
            throw new \ErrorException('Не удалось сохранить победителя ' . print_r($winnerContentElement->getErrors(), true));
        }

        $winnerContentElement->relatedPropertiesModel->setAttribute('winner_fio', $winner_fio);
        $winnerContentElement->relatedPropertiesModel->setAttribute('winner_phone', $winner_phone);
        $winnerContentElement->relatedPropertiesModel->setAttribute('winner_order_id', $winner_order_id);
        $winnerContentElement->relatedPropertiesModel->setAttribute('winner_product', $winner_product);
        $winnerContentElement->relatedPropertiesModel->setAttribute('winner_city', $winner_city);

        if (!$winnerContentElement->relatedPropertiesModel->save()) {
            throw new \ErrorException('Не удалось сохранить атрибуты победителя ' . print_r($winnerContentElement->relatedPropertiesModel->getErrors(), true));
        }

        return $winnerContentElement;
    }

    /**
     * форматирует имя и фамилию пользователя в формат "Имя Ф."
     * @param $fio
     * @return string
     */
    protected function formatFio($fio)
    {
        if (empty(trim($fio))) {
            return 'Неизвестный';
        }

        $parts = preg_split('/\s+/', $fio);

        $lastName = $parts[0] ?? '';
        $resultLastName = mb_strtoupper(mb_substr(trim($lastName), 0, 1));

        $resultName = mb_strtoupper(mb_substr($parts[1], 0, 1)) . mb_strtolower(mb_substr($parts[1], 1));

        return $resultName . ($resultLastName ? " {$resultLastName}." : '');
    }
}