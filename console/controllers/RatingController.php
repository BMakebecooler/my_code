<?php

/**
 * php ./yii rating/fake-make
 * php ./yii rating/calc
 *
 * @package console\controllers
 */

namespace console\controllers;

use common\lists\Contents;
use skeeks\cms\models\CmsContentElementProperty;
use Yii;
use yii\db\Connection;
use yii\helpers\Console;

class RatingController extends \yii\console\Controller
{

    /** @var Connection */
    protected $frontDb;

    /** @var  Connection */
    protected $db;


    const RATING_PARAM_ID = 178;

    public function init()
    {
        parent::init();

        $this->frontDb = null;// \Yii::$app->get('front_db');
        $this->db = \Yii::$app->get('db');
    }

    public function actionFakeMake(){
        $this->fake();
    }

    /**
     * Бутафорский рейтинг
     * @return int
     */
    protected function fake()
    {

        /**
         * SET @property_id = (SELECT id FROM cms_content_property WHERE `content_id` = '2' AND `code` = 'RATING');

        SELECT *
        FROM cms_content_element_property AS prop
        WHERE prop.property_id = 178 AND prop.element_id IN (

        SELECT cce.id
        FROM cms_content_element AS cce
        INNER JOIN ss_mediaplan_air_day_product_time AS air ON air.lot_id = cce.bitrix_id
        WHERE air.end_datetime >= UNIX_TIMESTAMP((CURRENT_DATE() - INTERVAL 3 DAY))
        GROUP BY cce.id
        )
         *
         */
        $insertSql = <<<SQL
-- SET @property_id = (SELECT id FROM cms_content_property WHERE `content_id` = '2' AND `code` = 'RATING'); 178

DELETE FROM `cms_content_element_property` WHERE `property_id` = :rating_param_id;

INSERT INTO cms_content_element_property (property_id, element_id, value, value_enum, value_num)
    SELECT :rating_param_id, t.product_id, t.value, t.value, t.value
    FROM (
        SELECT t.product_id, CEILING(t.value / 0.5) * 0.5 AS value
        FROM (
            SELECT product.id AS product_id, ROUND((RAND() * (5 - 2 + 1) + 2), 2) AS value
            FROM cms_content_element AS product
            WHERE product.content_id IN (2)
        ) AS t
    ) AS t;
    
UPDATE cms_content_element_property AS prop, (
    SELECT cce.id 
    FROM cms_content_element AS cce
    INNER JOIN ss_mediaplan_air_day_product_time AS air ON air.lot_id = cce.id
    WHERE air.end_datetime >= UNIX_TIMESTAMP((CURRENT_DATE() - INTERVAL 3 DAY))
    GROUP BY cce.id
) AS products
SET prop.value = 5,
    prop.value_enum = 5,
    prop.value_num = 5
WHERE prop.property_id = :rating_param_id AND prop.element_id = products.id;
SQL;

        return $this->db->createCommand($insertSql, [
            ':rating_param_id' => self::RATING_PARAM_ID
        ])->execute();
    }

    public function actionCalc()
    {
        $sql = <<<SQL

SELECT t.*
FROM (  
    SELECT
        id as bitrix_id,
        purchases / views as buy_conversion,
        (purchases + carts) / views as cart_conversion
    FROM (
        SELECT
         b.id,
         v.views,
         b.purchases,
         b.carts
        FROM b_iblock_element b
        LEFT JOIN (
         SELECT
            element_id,
            SUM(view_count) as views
            FROM b_catalog_viewed_product
            GROUP BY element_id
        ) v ON v.element_id=b.id     
        LEFT JOIN (
         SELECT product_id,
            SUM(
          CASE WHEN order_id IS NULL THEN NULL ELSE 1 END
         ) as purchases,
            SUM(
          CASE WHEN order_id IS NULL THEN 1 ELSE NULL END
         ) as carts
            FROM b_sale_basket
            GROUP BY product_id 
        ) b ON b.product_id=b.id
        WHERE b.iblock_id=10 AND views > 0 AND purchases > 0 AND carts > 0
    ) s
) AS t
ORDER BY cart_conversion DESC;
SQL;

        $blockElements = $this->frontDb->createCommand($sql)->queryAll();

        $propertyId = $this->db->createCommand("(SELECT id FROM cms_content_property WHERE `content_id` = '2' AND `code` = 'RATING')")->queryScalar();

        $inserts = [];
        $maxValue = null;
        $count = count($blockElements);
        $counter = 0;
        Console::startProgress(0, $count);

        foreach ($blockElements as $blockElement) {

            $counter++;
            Console::updateProgress($counter, $count);

            $cartConversion = $blockElement['cart_conversion'];
            $maxValue = ($maxValue) ? $maxValue : $cartConversion; // Первый элемент самый большой

            $percent = round(($cartConversion / $maxValue) * 100);

            // Минимальный рейтинг 2
            if ($percent < 20) {
                continue;
            }

            $value = round($percent / 20);

            $element = Contents::getContentElementByBitrixId($blockElement['bitrix_id'], [PRODUCT_CONTENT_ID, OFFERS_CONTENT_ID]);

            if ($element) {
                $inserts[] = [
                    'property_id' => $propertyId,
                    'element_id' => $element->id,
                    'value' => $value,
                    'value_enum' => $value,
                    'value_num' => $value,
                ];
            }
        }

        $this->db->createCommand("DELETE FROM `cms_content_element_property` WHERE `property_id` = " . $propertyId)->execute();

        Yii::$app->db->createCommand()->batchInsert(CmsContentElementProperty::tableName(), [
            'property_id',
            'element_id',
            'value',
            'value_enum',
            'value_num',
        ], $inserts)->execute();

        $countInsert = count($inserts);

        $this->stdout("Расчет рейтинга закончен. Вставлено - $countInsert записей\n", Console::FG_GREEN);
    }

}