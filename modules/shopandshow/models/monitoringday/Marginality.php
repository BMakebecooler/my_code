<?php

namespace modules\shopandshow\models\monitoringday;

use common\helpers\Math;
use modules\shopandshow\models\shop\ShopBasket;
use modules\shopandshow\models\shop\ShopOrder;

class Marginality extends \yii\base\Model
{

    public $date;
    public $factor = 0;

    public function init()
    {
        parent::init();

        if (!$this->date) {
            $this->date = date('Y-m-d');
        }

        $this->factor = floatval(\Yii::$app->shopAndShowSettings->monitoringDayFactor) / 100;

    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date'], 'required'],
            [['date'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'date' => 'Дата',
        ];
    }


    public function getData()
    {

        $factor = $this->factor;

        $query = <<<SQL
            SELECT (
              SELECT SUM(shop_basket.price * shop_basket.quantity) + SUM(shop_basket.price * shop_basket.quantity) * {$factor}
              FROM shop_basket
              LEFT JOIN shop_order ON shop_order.id = shop_basket.order_id
              WHERE (shop_order.created_at BETWEEN :start_date AND :end_date) AND shop_basket.has_removed = :has_removed
                AND shop_basket.order_id IS NOT NULL
            ) AS sum_all,  
                  
            (
              SELECT SUM(shop_basket.price * shop_basket.quantity) + SUM(shop_basket.price * shop_basket.quantity) * {$factor}
              FROM shop_basket
              INNER JOIN shop_order ON shop_order.id = shop_basket.order_id
              WHERE (shop_order.created_at BETWEEN :start_date AND :end_date) AND shop_basket.has_removed = :has_removed
                 AND shop_basket.order_id IS NOT NULL 
                AND shop_basket.main_product_id IN (
                  SELECT product_id
                  FROM ss_mediaplan_air_day_product_time AS adpt
                  INNER JOIN ss_mediaplan_air_blocks AS air_block ON air_block.block_id = adpt.block_id
                  WHERE air_block.begin_datetime >= :start_date AND air_block.begin_datetime <= :end_date
                )
            ) AS sum_efir, 
                  
            (
              SELECT SUM(shop_basket.price * shop_basket.quantity) + SUM(shop_basket.price * shop_basket.quantity) * {$factor}
              FROM shop_basket
              INNER JOIN shop_order ON shop_order.id = shop_basket.order_id
              WHERE (shop_order.created_at BETWEEN :start_date AND :end_date) AND shop_basket.has_removed = :has_removed
                AND  shop_basket.order_id IS NOT NULL AND shop_order.source = :source_basket_all
            ) AS basket_all,
                  
            (
              SELECT SUM(shop_basket.price * shop_basket.quantity) + SUM(shop_basket.price * shop_basket.quantity) * {$factor}
              FROM shop_basket
              INNER JOIN shop_order ON shop_order.id = shop_basket.order_id
              WHERE (shop_order.created_at BETWEEN :start_date AND :end_date) AND shop_basket.has_removed = :has_removed
                AND shop_basket.order_id IS NOT NULL AND shop_basket.main_product_id IN (
                  SELECT product_id
                  FROM ss_mediaplan_air_day_product_time AS adpt
                  INNER JOIN ss_mediaplan_air_blocks AS air_block ON air_block.block_id = adpt.block_id
                  WHERE air_block.begin_datetime >= :start_date AND air_block.begin_datetime <= :end_date
                )  AND shop_order.source = :source_basket_all
            ) AS basket_efir, 
                  
            (
              SELECT SUM(shop_basket.price * shop_basket.quantity) + SUM(shop_basket.price * shop_basket.quantity) * {$factor}
              FROM shop_basket
              INNER JOIN shop_order ON shop_order.id = shop_basket.order_id
              WHERE (shop_order.created_at BETWEEN :start_date AND :end_date) AND shop_basket.has_removed = :has_removed
                AND shop_basket.order_id IS NOT NULL AND shop_order.source_detail = :source_phone_6010
            ) AS phone6010_all,
                  
            (
              SELECT SUM(shop_basket.price * shop_basket.quantity) + SUM(shop_basket.price * shop_basket.quantity) * {$factor}
              FROM shop_basket
              INNER JOIN shop_order ON shop_order.id = shop_basket.order_id
              WHERE (shop_order.created_at BETWEEN :start_date AND :end_date) AND shop_basket.has_removed = :has_removed
                AND shop_basket.order_id IS NOT NULL AND shop_basket.main_product_id IN (
                  SELECT product_id
                  FROM ss_mediaplan_air_day_product_time AS adpt
                  INNER JOIN ss_mediaplan_air_blocks AS air_block ON air_block.block_id = adpt.block_id
                  WHERE air_block.begin_datetime >= :start_date AND air_block.begin_datetime <= :end_date
                )  AND shop_order.source_detail = :source_phone_6010
            ) AS phone6010_efir,
            
            (
              SELECT SUM((shop_basket.price - if(margin.value is null or margin.value = '' or margin.value = 0, shop_basket.price, margin.value)) * shop_basket.quantity)
                + SUM((shop_basket.price - if(margin.value is null or margin.value = '' or margin.value = 0, shop_basket.price, margin.value)) * shop_basket.quantity) * {$factor}
              FROM shop_basket
              INNER JOIN shop_order ON shop_order.id = shop_basket.order_id
              
              LEFT JOIN cms_content_element_property AS margin ON margin.element_id = shop_basket.main_product_id AND margin.property_id = 
                  (SELECT id FROM cms_content_property WHERE code = 'PURCHASE_PRICE')
                  
              WHERE (shop_order.created_at BETWEEN :start_date AND :end_date) AND shop_basket.has_removed = :has_removed
                AND  shop_basket.order_id IS NOT NULL
            ) AS marga_all,  
                  
            (
              SELECT SUM((shop_basket.price - if(margin.value is null or margin.value = '' or margin.value = 0, shop_basket.price, margin.value)) * shop_basket.quantity)
                + SUM((shop_basket.price - if(margin.value is null or margin.value = '' or margin.value = 0, shop_basket.price, margin.value)) * shop_basket.quantity) * {$factor}
              FROM shop_basket
              INNER JOIN shop_order ON shop_order.id = shop_basket.order_id   

              LEFT JOIN cms_content_element_property AS margin ON margin.element_id = shop_basket.main_product_id AND margin.property_id = 
                  (SELECT id FROM cms_content_property WHERE code = 'PURCHASE_PRICE')
                  
              WHERE (shop_order.created_at BETWEEN :start_date AND :end_date) AND shop_basket.has_removed = :has_removed
                AND shop_basket.order_id IS NOT NULL AND shop_basket.main_product_id IN (
                  SELECT product_id
                  FROM ss_mediaplan_air_day_product_time AS adpt
                  INNER JOIN ss_mediaplan_air_blocks AS air_block ON air_block.block_id = adpt.block_id
                  WHERE air_block.begin_datetime >= :start_date AND air_block.begin_datetime <= :end_date
                )
            ) AS marga_efir

SQL;


        $data = \Yii::$app->db->createCommand($query, [
            ':start_date' => $this->getPeriodBegin($this->date),
            ':end_date' => $this->getPeriodEnd($this->date),
            ':has_removed' => ShopBasket::HAS_REMOVED_FALSE,
            ':source_basket_all' => ShopOrder::SOURCE_SITE,
            ':source_phone_6010' => ShopOrder::SOURCE_DETAIL_PHONE2,
        ])->queryOne();

        $sumAll = $data['sum_all'];
        $sumEfir = $data['sum_efir'];
        $sumNotEfir = $sumAll - $sumEfir;

        $basketAll = $data['basket_all'];
        $basketEfir = $data['basket_efir'];
        $basketNotEfir = $basketAll - $basketEfir;

        $phone6010All = $data['phone6010_all'];
        $phone6010Efir = $data['phone6010_efir'];
        $phone6010NotEfir = $phone6010All - $phone6010Efir;

        $margaAll = $data['marga_all'];
        $margaEfir = $data['marga_efir'];
        $margaNotEfir = $margaAll - $margaEfir;


        $marginalityAll = Math::percent($sumAll, $margaAll);
        $marginalityEfir = Math::percent($sumEfir, $margaEfir);
        $marginalityNotEfir = Math::percent($sumNotEfir, $margaNotEfir);

        $numberFormat = function ($number, $decimals = 0) {
            return number_format($number, $decimals, ',', ' ');
        };

        $result = [
            'sum_all' => $numberFormat($sumAll),
            'sum_efir' => $numberFormat($sumEfir),
            'sum_not_efir' => $numberFormat($sumNotEfir),

            'basket_all' => $numberFormat($basketAll),
            'basket_efir' => $numberFormat($basketEfir),
            'basket_not_efir' => $numberFormat($basketNotEfir),

            'phone6010_all' => $numberFormat($phone6010All),
            'phone6010_efir' => $numberFormat($phone6010Efir),
            'phone6010_not_efir' => $numberFormat($phone6010NotEfir),

            'marga_all' => $numberFormat($margaAll),
            'marga_efir' => $numberFormat($margaEfir),
            'marga_not_efir' => $numberFormat($margaNotEfir),

            'marginality_all' => $numberFormat($marginalityAll, 2),
            'marginality_efir' => $numberFormat($marginalityEfir, 2),
            'marginality_not_efir' => $numberFormat($marginalityNotEfir, 2),
        ];

        return $result;
    }

    private function getPeriodBegin($date)
    {
        return strtotime(sprintf('%s 00:00:00', $date));
    }

    private function getPeriodEnd($date)
    {
        return strtotime(sprintf('%s 23:59:59', $date));
    }
}