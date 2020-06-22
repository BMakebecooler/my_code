<?php

/**
 * php ./yii kfss/prices
 * php ./yii kfss/prices/prices-index
 */

namespace console\controllers\kfss;

use console\controllers\sync\SyncController;
use yii\helpers\Console;


/**
 * Class PricesController
 *
 * @package console\controllers
 */
class PricesController extends SyncController
{

    public function actionIndex()
    {
        return false;
        //Пересчет цены ШШ
        $this->actionRecalculatePriceType();

        //Актуализация цен
        $this->actionPricesIndex();
    }

    /**
     * index rebuild
     * @return bool
     * @throws \yii\db\Exception
     */
    public function actionPricesIndex()
    {
        return false;

        //\Yii::$app->db->createCommand("SET sql_mode = '';")->execute();

        $basePriceRow = \Yii::$app->db->createCommand("SELECT id FROM `shop_type_price` WHERE `code` = 'BASE'")->queryOne();
        $basePriceId = $basePriceRow['id'];

        $sSPriceRow = \Yii::$app->db->createCommand("SELECT id FROM `shop_type_price` WHERE `code` = 'SHOPANDSHOW'")->queryOne();
        $sSPriceId = $sSPriceRow ? $sSPriceRow['id'] : $basePriceId;

        $query = <<<SQL
INSERT INTO ss_shop_product_prices (product_id, type_price_id, price, min_price, max_price, discount_percent)
    SELECT t.*, GREATEST(0, ROUND(((max_price - price) / max_price) * 100 )) AS discount_percent
    FROM (
        SELECT product_id, type_price_id, IF(price = 0, min_price, price) AS price, min_price, max_price
        FROM (
        SELECT product.id AS product_id, COALESCE(stp_offer.id, {$basePriceId}) AS type_price_id, 
        
            CASE WHEN product.content_id = 2 THEN
              COALESCE(
                NULLIF(MIN(
                  IF(product.prices_vary=1, sp_min_offer.price, COALESCE(sp_min_offer.price, sp_min_product.price))
                ), ''),
                COALESCE(spp_offer.price, spp_base.price)
              )
            ELSE 
              COALESCE(spp_offer.price, spp_base.price)
            END AS price,
        
            COALESCE(NULLIF(MIN(IF(product.content_id=2 AND product.prices_vary=1, sp_min_offer.price, COALESCE(sp_min_offer.price, sp_min_product.price))), ''), COALESCE(spp_offer.price, spp_base.price)) AS min_price, 
            MIN(IF(product.content_id=2 AND product.prices_vary=1, sp_max_offer.price, COALESCE(sp_max_offer.price, sp_max_product.price))) AS max_price
              
            FROM (
              SELECT product.*, prices_vary.value AS prices_vary
              FROM cms_content_element AS product
                INNER JOIN shop_product AS sp_product ON sp_product.id = product.id AND sp_product.quantity > 0
                LEFT JOIN cms_content_element_property AS prices_vary ON product.id = prices_vary.element_id AND prices_vary.property_id=196
              WHERE product.active = 'Y' AND product.content_id IN (2, 10) -- AND product.id = 217921 -- Для отладки
            ) AS product 
            
            LEFT JOIN cms_content_element parent_card 
                   ON parent_card.id = product.parent_content_element_id 
                  AND product.content_id = 10
                
            LEFT JOIN cms_content_element child_cards ON child_cards.parent_content_element_id = product.id AND child_cards.active = 'Y' AND child_cards.content_id = 5
            LEFT JOIN (
               SELECT child_products.* FROM cms_content_element AS child_products 
               INNER JOIN shop_product AS sp_offer ON sp_offer.id = child_products.id AND sp_offer.quantity > 0
               WHERE child_products.active = 'Y' AND child_products.content_id = 10
            ) AS child_products ON child_products.parent_content_element_id = child_cards.id 

            
            LEFT JOIN cms_content_element_property price_active_id ON price_active_id.element_id = COALESCE(parent_card.parent_content_element_id, product.id)
               AND price_active_id.property_id = (SELECT id FROM cms_content_property WHERE code = 'PRICE_ACTIVE' AND content_id = 2)
               
            LEFT JOIN shop_type_price AS stp_offer ON stp_offer.id = price_active_id.value
        
            LEFT JOIN shop_product_price AS spp_offer ON spp_offer.product_id = product.id AND spp_offer.type_price_id = stp_offer.id
            LEFT JOIN shop_product_price AS spp_base ON spp_base.type_price_id = {$basePriceId} AND spp_base.product_id = product.id
            
            -- Минимальная цена товаров и предложений
            LEFT JOIN shop_product_price AS sp_min_offer ON sp_min_offer.product_id IN (child_products.id) AND sp_min_offer.price > 0  
                  AND sp_min_offer.type_price_id = COALESCE(stp_offer.id, {$basePriceId})
            LEFT JOIN shop_product_price AS sp_min_product ON sp_min_product.product_id = product.id AND sp_min_product.price > 0 
                  AND sp_min_product.type_price_id = COALESCE(stp_offer.id, {$basePriceId})

            
            -- Максимальная цена товаров и предложений от типа цена ШШ
            LEFT JOIN shop_product_price AS sp_max_offer ON sp_max_offer.product_id IN (child_products.id) AND sp_max_offer.price > 0  
                  AND sp_max_offer.type_price_id = {$sSPriceId}
            LEFT JOIN shop_product_price AS sp_max_product ON sp_max_product.product_id = product.id AND sp_max_product.price > 0 
                  AND sp_max_product.type_price_id = {$sSPriceId}
            
            GROUP BY product.id 
        ) AS t
    ) AS t
ON DUPLICATE KEY UPDATE type_price_id=VALUES(type_price_id),  price=VALUES(price),  min_price=VALUES(min_price),  max_price=VALUES(max_price),  discount_percent=VALUES(discount_percent)
SQL;


        try {

            $transaction = \Yii::$app->db->beginTransaction();

            $affected = \Yii::$app->db->createCommand($query)->execute();

            $transaction->commit();

            $this->stdout("PriceIndex affected rows: {$affected}", Console::FG_YELLOW);

        } catch (\yii\db\Exception $e) {

            $this->stdout($e->getMessage(), Console::FG_RED);

            return false;
            //$transaction->rollBack();
//            throw $e;
            //return false;

        }

        return true;

    }

    /** Recalculate Price type ShopAndShow
     * - если активная цена Цена Шопэндшоу -> цена ШШ = цена Базовая
     * - если активная любая другая -> цена ШШ рассчитывается по формуле (см. в запросе) - отображается в ЗАЧЕРКНУТОЙ цене */
    public function actionRecalculatePriceType()
    {

        $this->stdout("Recalculate SHOPANDSHOW price type" . PHP_EOL, Console::FG_YELLOW);

        $koef = 1.2;

        $basePriceRow = \Yii::$app->db->createCommand("SELECT id FROM `shop_type_price` WHERE `code` = 'BASE'")->queryOne();
        $basePriceId = $basePriceRow['id'];

        $sSPriceRow = \Yii::$app->db->createCommand("SELECT id FROM `shop_type_price` WHERE `code` = 'SHOPANDSHOW'")->queryOne();
        $sSPriceId = $sSPriceRow ? $sSPriceRow['id'] : $basePriceId;

        $productContentId = PRODUCT_CONTENT_ID;
        $offerContentId = OFFERS_CONTENT_ID;

        $query = "
            INSERT INTO shop_product_price (created_at, updated_at, product_id, type_price_id, price, currency_code)
                SELECT
                    NOW() AS created_at,
                    NOW() AS updated_at,
                    product_price.product_id,
                    {$sSPriceId} as type_price_id,
                    CASE WHEN element.content_id=2 THEN
                      IF ( product_price_active.value IS NOT NULL AND product_price_active.value != {$basePriceId}, ROUND( ROUND(product_price.price * {$koef} / 100) * 100 - 1), product_price.price)
                    ELSE 
                      IF ( offer_price_active.value IS NOT NULL AND offer_price_active.value != {$basePriceId}, ROUND( ROUND(product_price.price * {$koef} / 100) * 100 - 1), product_price.price)
                    END as price,
                    'RUB' as currency_code
                FROM shop_product_price product_price
                    LEFT JOIN shop_product product ON product.id=product_price.product_id
                    LEFT JOIN cms_content_element element ON element.id=product.id AND element.content_id IN ({$productContentId}, {$offerContentId})
                    LEFT JOIN cms_content_element_property product_price_active ON product_price_active.element_id=element.id and product_price_active.property_id=82 
                    LEFT JOIN cms_content_element_property offer_price_active ON offer_price_active.element_id=element.id and offer_price_active.property_id=174
                WHERE
                    product_price.type_price_id={$basePriceId}
            ON DUPLICATE KEY UPDATE price=VALUES(price), updated_at=NOW()
        ";

        try {
            $transaction = \Yii::$app->db->beginTransaction();
            $affected = \Yii::$app->db->createCommand($query)->execute();
            $transaction->commit();
            $this->stdout("RecalculatePriceType affected rows: {$affected}" . PHP_EOL, Console::FG_YELLOW);
        } catch (\yii\db\Exception $e) {
            $this->stdout($e->getMessage(), Console::FG_RED);
            return false;
        }

        $this->stdout("DONE" . PHP_EOL, Console::FG_GREEN);

        return true;
    }
}


/** SQL DEBUG
 *
 *
 * select
 * b.id,
 * ce.bitrix_id,
 * b.name,
 * ce.id,
 * ce.name,
 * pp.id,
 * pp.code,
 * stp.id,
 * stp.code,
 * ep.value_num
 * from front2.b_iblock_element b
 * left join front2.b_iblock_element_property ep ON ep.iblock_element_id=b.id
 * left join front2.b_iblock_property pp ON pp.id=ep.iblock_property_id
 * left join shop_type_price stp ON stp.code=REPLACE(pp.code,'PRICE_','')
 * left join cms_content_element ce ON ce.bitrix_id=b.id
 * left join shop_product sp ON sp.id=ce.id
 * where
 * b.iblock_id=10
 * and sp.id is not null
 * and b.id in (select bitrix_id from cms_content_element where content_id=2)
 * AND ep.iblock_property_id IN ( select id from front2.b_iblock_property where code IN ('PRICE_BASE','PRICE_SALE','PRICE_DISCOUNTED','PRICE_PRIVATE_SALE','PRICE_TODAY'))
 * order by b.id desc
 * limit 0,100;
 *
 *
 *
 */


/** ОСТАВИЛ СТРЫЙ КОД ПОКА ТУТ. ПРОСТО Н ВСЯКИЙ СЛУЧАЙ
 *
 * protected function fillOffersPrices()
 * {
 *
 * $query = "
 * INSERT INTO shop_product_price (id, created_at, updated_at, product_id, type_price_id, price, currency_code)
 * select
 * ifnull(exists_value.id, 'default') as id,
 * spp.created_at,
 * spp.updated_at,
 * ce.id,
 * spp.type_price_id,
 * spp.price,
 * 'RUB'
 * from cms_content_element ce
 * left join cms_content_element parent ON parent.id=ce.parent_content_element_id
 * left join shop_product_price spp ON spp.product_id=parent.id
 * left join shop_product_price offer_price ON offer_price.product_id=ce.id
 * LEFT JOIN shop_product_price exists_value ON exists_value.product_id=ce.id and exists_value.type_price_id=spp.type_price_id
 * where
 * ce.parent_content_element_id is not null
 * and offer_price.product_id is null
 * and ce.content_id in (10)
 * and spp.type_price_id in (select id from shop_type_price)
 * ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at), price=VALUES(price)
 * ";
 *
 * $this->stdout("Syncing offers prices ", Console::FG_YELLOW);
 *
 * try {
 *
 * $transaction = \Yii::$app->db->beginTransaction();
 *
 * $affected = \Yii::$app->db->createCommand($query)->execute();
 * $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);
 *
 * $transaction->commit();
 *
 * } catch (\yii\db\Exception $e) {
 *
 * $this->logError($e, $query);
 *
 * $transaction->rollBack();
 * return false;
 *
 * }
 *
 * return true;
 *
 * }
 *
 * protected function fillPriceShopAndShowValue()
 * {
 *
 * $koef = 1.2;
 *
 * $query = "
 * INSERT INTO shop_product_price (id, created_at, updated_at, product_id, type_price_id, price, currency_code)
 * select
 * ifnull(exists_value.id, 'default') as id,
 * UNIX_TIMESTAMP(),
 * UNIX_TIMESTAMP(),
 * spp.product_id,
 * (SELECT id FROM shop_type_price WHERE code = 'SHOPANDSHOW'),
 * ROUND(spp.price * {$koef}),
 * 'RUB'
 * from shop_product sp
 * left join shop_product_price spp ON spp.product_id=sp.id AND spp.type_price_id=4
 * LEFT JOIN shop_product_price exists_value ON exists_value.product_id=spp.product_id and exists_value.type_price_id=(SELECT id FROM shop_type_price WHERE code = 'SHOPANDSHOW')
 * where spp.product_id IS NOT NULL
 * ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at), price=VALUES(price)
 * ";
 *
 * $this->stdout("Recalculate PRICE_SHOPANDSHOW ", Console::FG_YELLOW);
 *
 * try {
 *
 * $transaction = \Yii::$app->db->beginTransaction();
 *
 * $affected = \Yii::$app->db->createCommand($query)->execute();
 * $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);
 *
 * $transaction->commit();
 *
 * } catch (\yii\db\Exception $e) {
 *
 * $this->logError($e, $query);
 *
 * $transaction->rollBack();
 * return false;
 *
 * }
 *
 * return true;
 *
 *
 * }
 *
 * protected function offersPrices()
 * {
 *
 * $query = "
 * INSERT INTO shop_product_price (id, created_at, updated_at, product_id, type_price_id, price, currency_code)
 * select
 * ifnull(exists_value.id, 'default') as id,
 * UNIX_TIMESTAMP(),
 * UNIX_TIMESTAMP(),
 * sp.id,
 * stp.id,
 * ep.value_num,
 * 'RUB'
 * from front2.b_iblock_element b
 * left join front2.b_iblock_element_property ep ON ep.iblock_element_id=b.id
 * left join front2.b_iblock_property pp ON pp.id=ep.iblock_property_id
 * left join shop_type_price stp ON stp.code=REPLACE(pp.code,'PRICE_','')
 * left join cms_content_element ce ON ce.bitrix_id=b.id
 * left join shop_product sp ON sp.id=ce.id
 * LEFT JOIN shop_product_price exists_value ON exists_value.product_id=ce.id and exists_value.type_price_id=stp.id
 * where
 * b.iblock_id=11
 * and sp.id IS NOT NULL
 * and b.id in (select bitrix_id from cms_content_element where content_id=10)
 * AND ep.iblock_property_id IN ( select id from front2.b_iblock_property where code IN ('PRICE_BASE','PRICE_SALE','PRICE_DISCOUNTED','PRICE_TODAY'))
 * ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at), price=VALUES(price)
 * ";
 *
 * $this->stdout("Syncing offers prices ", Console::FG_YELLOW);
 *
 * try {
 *
 * $transaction = \Yii::$app->db->beginTransaction();
 *
 * $affected = \Yii::$app->db->createCommand($query)->execute();
 * $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);
 *
 * $transaction->commit();
 *
 * } catch (\yii\db\Exception $e) {
 *
 * $this->logError($e, $query);
 *
 * $transaction->rollBack();
 * return false;
 *
 * }
 *
 * return true;
 *
 * }
 */
