<?php

/**
 * php ./yii sync/prices
 * php ./yii sync/prices/active-price
 * php ./yii sync/prices/prices-index
 * php ./yii sync/prices/recalculate-price-type
 */

namespace console\controllers\sync;

use common\helpers\Developers;
use common\helpers\Msg;
use modules\shopandshow\models\newEntities\products\PricesList;
use skeeks\cms\agent\models\CmsAgent;
use skeeks\cms\components\Cms;
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

        /**
         * Для того чтобы если запустили в ручную пересчитать время следующего обновления
         * @param int $time
         */
        $updatedTime = function ($time = 270) {
            if ($agent = CmsAgent::find()->andWhere("name = 'sync/prices'")->one()) {
                $agent->is_running = Cms::BOOL_N;
                $agent->next_exec_at = time() + $time; // повторим через минуту
                $agent->save();
            }
        };

        $transaction = \Yii::$app->db->beginTransaction();

        try {

            $this->runAll();

            \Yii::$app->shares->removeThumbs(); //Перегенерируем баннеры

            $transaction->commit();
        } catch (\yii\db\Exception $e) {

            $transaction->rollBack();

//            Developers::reportProblem('Проблема в синхронизации цен!' . $e->getMessage());

            $this->stdout("Перезапустим агент через минуту\n", Console::FG_RED);

            \Yii::error('ss Импорт цен не прошел! Перезапустим агент через минуту!' . $e->getMessage());

            $updatedTime();

            return false;
        }

        $updatedTime(4000);
    }

    protected function runAll()
    {

        $this->cleanPricesTable();

        /** Sync product/offer prices */
        $this->syncPrices();

        /** Sync Active price */
        $this->OLD_actionActivePrice();


        /** Recalculate Price type ShopAndShow
         * - если активная цена Цена Шопэндшоу -> цена ШШ = цена Базовая
         * - если активная любая другая -> цена ШШ рассчитывается по формуле (см. в запросе) - отображается в ЗАЧЕРКНУТОЙ цене */
        $this->actionRecalculatePriceType();

        /** Rebuild index */
        $this->actionPricesIndex();

        \Yii::$app->cache->flush(); //Сбрасываем кеш после каждого импорта цен
    }

    protected function getPricesLinks()
    {

        try {
            $this->fixTableCollation('shop_type_price');
        } catch (\yii\db\Exception $e) {
            $this->stdout("Can't change collation for table shop_type_price\n", Console::FG_RED);
            return false;
        }

        $query = "
            select
                stp.id as type_price_id,
                stp.code as price_code,
                ibp.id as bitrix_property_id,
                ibp.code bitrix_price_code,
                CASE WHEN ibp.iblock_id=" . SyncController::BITRIX_PRODUCT_BLOCK_ID . " THEN " . SyncController::LOCAL_PRODUCT_BLOCK_ID . " ELSE
                 CASE WHEN ibp.iblock_id=" . SyncController::BITRIX_OFFERS_BLOCK_ID . " THEN " . SyncController::LOCAL_OFFERS_BLOCK_ID . " END END as content_id
            from shop_type_price stp
            left join front2.b_iblock_property ibp ON ibp.code=CONCAT('PRICE_',stp.code)
        ";

        return \Yii::$app->db->createCommand($query)->queryAll();

    }

    protected function cleanPricesTable()
    {

        $this->stdout("Delete exist prices", Console::FG_YELLOW);


        for ($x = 1; $x <= 90; $x++) {
            if ($affected = \Yii::$app->db->createCommand("DELETE FROM shop_product_price WHERE type_price_id IN (SELECT id FROM shop_type_price) LIMIT 20000")->execute()) {
                $this->stdout(" ok. Affected {$affected} rows\n", Console::FG_GREEN);
                usleep(148);
            }
        }

        return true;
    }

    protected function syncPrices()
    {

        $links = $this->getPricesLinks();

        $query = "
            insert IGNORE into shop_product_price (created_at, updated_at, product_id, type_price_id, price, currency_code)
            select
                unix_timestamp() as created_at,
                unix_timestamp() as updated_at,
                p.id as product_id,
                :TYPE_PRICE_ID as type_price_id,
                ifnull(bp.value_num, 0) as price,
                'RUB' as currency_code
            from shop_product p
            left join cms_content_element e ON e.id=p.id
            left join front2.b_iblock_element b ON b.id=e.bitrix_id
            left join front2.b_iblock_element_property bp ON bp.iblock_element_id=b.id AND bp.iblock_property_id=:BITRIX_PROPERTY_ID
            where
                e.content_id=:CONTENT_ID
        ";

        foreach ($links as $link) {

            if ($link['content_id'] != SyncController::LOCAL_PRODUCT_BLOCK_ID || $link['price_code'] == 'SHOPANDSHOW')
                continue;

            $this->stdout("Syncing {$link['price_code']} price type for content id {$link['content_id']}\n", Console::FG_CYAN);
            $this->stdout("Link config:\n", Console::FG_YELLOW);
            $this->stdout("    Birtix price code: " . $link['bitrix_price_code'] . "\n", Console::FG_GREEN);
            $this->stdout("    Cms price code: " . $link['price_code'] . "\n", Console::FG_GREEN);
            $this->stdout("    Birtix property id: " . $link['bitrix_property_id'] . "\n", Console::FG_GREEN);
            $this->stdout("    Content id: " . $link['content_id'] . "\n", Console::FG_GREEN);
            $this->stdout("    Shop price type id: " . $link['type_price_id'] . "\n", Console::FG_GREEN);

            $this->stdout("Syncing prices", Console::FG_YELLOW);

            try {

                //$transaction = \Yii::$app->db->beginTransaction();

                $params = [
                    ':TYPE_PRICE_ID' => $link['type_price_id'],
                    ':BITRIX_PROPERTY_ID' => $link['bitrix_property_id'],
                    ':CONTENT_ID' => $link['content_id']
                ];

                $affected = \Yii::$app->db->createCommand($query, $params)->execute();
                $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

                //$transaction->commit();

            } catch (\yii\db\Exception $e) {

                $this->logError($e, $query);

                //$transaction->rollBack();
                throw $e;
                //return false;

            }

        }

        $query = "
            insert into shop_product_price (created_at, updated_at, product_id, type_price_id, price, currency_code)
            select
                unix_timestamp() as created_at,
                unix_timestamp() as updated_at,
                p.id as product_id,
                :TYPE_PRICE_ID as type_price_id,
                CASE WHEN price_mods_diff.value=34 THEN ifnull(bp.value_num, 0) ELSE
                    ifnull(parent_price.price, 0) END as price,
                'RUB' as currency_code
            from shop_product p
            left join cms_content_element e ON e.id=p.id
            left join cms_content_element eparent ON eparent.id=e.parent_content_element_id
            left join shop_product_price parent_price ON parent_price.product_id=eparent.id and parent_price.type_price_id=:TYPE_PRICE_ID
            left join front2.b_iblock_element b ON b.id=e.bitrix_id
            left join front2.b_iblock_element_property bp ON bp.iblock_element_id=b.id AND bp.iblock_property_id=:BITRIX_PROPERTY_ID
            left join front2.b_iblock_element_property price_mods_diff ON price_mods_diff.iblock_element_id=eparent.bitrix_id AND price_mods_diff.iblock_property_id=93
            where
                e.content_id=:CONTENT_ID
            on duplicate key update updated_at=unix_timestamp(), price=VALUES(price)
        ";

        foreach ($links as $link) {

            if ($link['content_id'] != SyncController::LOCAL_OFFERS_BLOCK_ID || $link['price_code'] == 'SHOPANDSHOW')
                continue;

            $this->stdout("Syncing {$link['price_code']} price type for content id {$link['content_id']}\n", Console::FG_CYAN);
            $this->stdout("Link config:\n", Console::FG_YELLOW);
            $this->stdout("    Birtix price code: " . $link['bitrix_price_code'] . "\n", Console::FG_GREEN);
            $this->stdout("    Cms price code: " . $link['price_code'] . "\n", Console::FG_GREEN);
            $this->stdout("    Birtix property id: " . $link['bitrix_property_id'] . "\n", Console::FG_GREEN);
            $this->stdout("    Content id: " . $link['content_id'] . "\n", Console::FG_GREEN);
            $this->stdout("    Shop price type id: " . $link['type_price_id'] . "\n", Console::FG_GREEN);

            $this->stdout("Syncing prices", Console::FG_YELLOW);

            try {

                //$transaction = \Yii::$app->db->beginTransaction();

                $params = [
                    ':TYPE_PRICE_ID' => $link['type_price_id'],
                    ':BITRIX_PROPERTY_ID' => $link['bitrix_property_id'],
                    ':CONTENT_ID' => $link['content_id']
                ];

                $command = \Yii::$app->db->createCommand($query, $params);

                //$this->stdout($command->rawSql . PHP_EOL, Console::FG_CYAN);

                $affected = $command->execute();
                $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

                //$transaction->commit();

            } catch (\yii\db\Exception $e) {

                $this->logError($e, $query);

                //$transaction->rollBack();
                throw $e;
                //return false;

            }

        }


        return true;

    }

    public function actionRecalculatePriceType()
    {

        $this->stdout("Recalculate SHOPANDSHOW price type\n", Console::FG_YELLOW);

        $this->stdout("Delete current SHOPANDSHOW price type values", Console::FG_YELLOW);

        if ($affected = \Yii::$app->db->createCommand("DELETE FROM shop_product_price WHERE type_price_id = (SELECT id FROM shop_type_price WHERE code = 'SHOPANDSHOW')")->execute())
            $this->stdout(" ok. Affected {$affected} rows\n", Console::FG_GREEN);


        $query = "
            insert into shop_product_price (product_id, type_price_id, price, currency_code)
                select
                    p.product_id,
                    6 as type_price_id,
                    CASE WHEN e.content_id=2 THEN
                      IF ( ce.value IS NOT NULL, ROUND( ROUND(p.price * 1.2 / 100) * 100 - 1), p.price)
                    ELSE 
                      IF ( pe.value IS NOT NULL, ROUND( ROUND(p.price * 1.2 / 100) * 100 - 1), p.price)
                    END as price,
                    'RUB' as currency_code
                from shop_product_price p
                left join shop_product product ON product.id=p.product_id
                left join cms_content_element e ON e.id=product.id AND e.content_id IN (2,10)
                left join cms_content_element_property ce ON ce.element_id=e.id and ce.property_id=82 
                left join cms_content_element_property pe ON pe.element_id=e.id and pe.property_id=174
                where
                    p.type_price_id=4
            on duplicate key update price=VALUES(price), updated_at=CURRENT_TIMESTAMP
              
        ";

        $this->stdout("Inserting", Console::FG_YELLOW);

        try {

            //$transaction = \Yii::$app->db->beginTransaction();

            $affected = \Yii::$app->db->createCommand($query)->execute();
            $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

            //$transaction->commit();

        } catch (\yii\db\Exception $e) {

            $this->logError($e, $query);

            //$transaction->rollBack();
            throw $e;
            //return false;

        }
        return true;
    }

    public function OLD_actionActivePrice()
    {

        $this->stdout("Deleting ACTIVE PRICE prop values\n", Console::FG_YELLOW);

        try {

            //$transaction = \Yii::$app->db->beginTransaction();

            $activePricePropId = \Yii::$app->db->createCommand('SELECT id,code FROM `cms_content_property` WHERE `code` = "PRICE_ACTIVE" and `content_id` IN (2,10)')->queryAll();

            foreach ($activePricePropId as $prop) {

                $this->stdout("Deleting prop values for: " . $prop['code']);

                $affected = \Yii::$app->db->createCommand('DELETE FROM cms_content_element_property WHERE property_id=' . $prop['id'])->execute();
                $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

            }

            //$transaction->commit();

        } catch (\yii\db\Exception $e) {

            //$transaction->rollBack();
            throw $e;
            //return false;

        }

        // DISTINCT нужен, т.к. в самом битриксе имеются дубли в b_iblock_element_property
        $query = "
                INSERT INTO cms_content_element_property (created_at, updated_at, property_id, element_id, value)
                  SELECT DISTINCT
                    UNIX_TIMESTAMP() as created_at,
                    UNIX_TIMESTAMP() as updated_at,
                        cp.id as property_id,
                        ce.id as element_id,
                        rep.id as value
                    FROM front2.b_iblock_element b
                    LEFT JOIN front2.b_iblock_element_property bep ON bep.IBLOCK_ELEMENT_ID=b.ID AND bep.IBLOCK_PROPERTY_ID=88
                    LEFT JOIN cms_content_property cp ON cp.vendor_id = bep.IBLOCK_PROPERTY_ID
                    LEFT JOIN cms_content_element rep ON rep.bitrix_id=bep.VALUE AND rep.content_id=(select id from cms_content where code='PRICE_ACTIVE') 
                    LEFT JOIN cms_content_element ce ON ce.bitrix_id = bep.IBLOCK_ELEMENT_ID and ce.content_id=2
                    WHERE
                            bep.VALUE is not null
                            and ce.id is not null
                            and bep.IBLOCK_PROPERTY_ID=88
                ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at), value=VALUES(value)
        ";

        $this->stdout("Syncing ACTIVE PRICE property ", Console::FG_YELLOW);

        try {

            //$transaction = \Yii::$app->db->beginTransaction();

            $affected = \Yii::$app->db->createCommand($query)->execute();
            $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

            //$transaction->commit();

        } catch (\yii\db\Exception $e) {

            $this->logError($e, $query);

            //$transaction->rollBack();
            throw $e;
            //return false;

        }

        $query = "
                INSERT INTO cms_content_element_property (created_at, updated_at, property_id, element_id, value)
                  select
                        unix_timestamp(),
                        unix_timestamp(),
                        174,
                        e.id,
                        ep.value
                    from cms_content_element e
                    left join cms_content_element parent ON parent.id=e.parent_content_element_id
                    left join cms_content_element_property ep ON ep.element_id=parent.id AND ep.property_id=82
                    where e.content_id=10
                ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at), value=VALUES(value)
        ";

        $this->stdout("Syncing OFFERS ACTIVE PRICE property ", Console::FG_YELLOW);

        try {

            //$transaction = \Yii::$app->db->beginTransaction();

            $affected = \Yii::$app->db->createCommand($query)->execute();
            $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

            //$transaction->commit();

        } catch (\yii\db\Exception $e) {

            $this->logError($e, $query);

            //$transaction->rollBack();
            throw $e;
            //return false;

        }

        return true;

    }

    /** ОСТАВИЛ ЭТО ПОКА ТУТ ИБО НАДО РАЗОБРАТЬСЯ */

    protected function recalculateShopAndShowPriceType()
    {

        return false;

        $this->stdout("Recalculate SHOPANDSHOW price type\n", Console::FG_YELLOW);

        \Yii::$app->db->createCommand("SET @base_price_id = (SELECT id FROM `shop_type_price` WHERE `code` = 'BASE');")->execute();
        \Yii::$app->db->createCommand("SET @ss_price_id = (SELECT id FROM `shop_type_price` WHERE `code` = 'SHOPANDSHOW');")->execute();

        $query = "
            insert into shop_product_price (id, product_id, type_price_id, price, currency_code)
                select
                    ifnull(exists_price.id, 'default'),
                    p.product_id,
                    @ss_price_id as type_price_id,
                    CASE WHEN e.content_id=2 THEN
                      IF ( ce.value IS NOT NULL AND ce.value != '', ROUND( ROUND(p.price * 1.2 / 100) * 100 - 1), p.price)
                    ELSE 
                      IF ( pe.value IS NOT NULL AND pe.value != '', ROUND( ROUND(p.price * 1.2 / 100) * 100 - 1), p.price)
                    END as price,
                    'RUB' as currency_code
                from shop_product_price p
                left join shop_product product ON product.id=p.product_id
                left join cms_content_element e ON e.id=product.id AND e.content_id IN (2,10)
                left join cms_content_element_property ce ON ce.element_id=e.id and ce.property_id=82 
                left join cms_content_element_property pe ON pe.element_id=e.id and pe.property_id=174
                left join shop_product_price exists_price ON exists_price.product_id = p.product_id and exists_price.type_price_id = @ss_price_id
                where
                    p.price > 1
                    and p.type_price_id=@base_price_id
            on duplicate key update price=VALUES(price), updated_at=CURRENT_TIMESTAMP
              
        ";

        $this->stdout("Inserting", Console::FG_YELLOW);

        try {

            //$transaction = \Yii::$app->db->beginTransaction();

            $affected = \Yii::$app->db->createCommand($query)->execute();
            $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

            //$transaction->commit();

        } catch (\yii\db\Exception $e) {

            $this->logError($e, $query);

            //$transaction->rollBack();
            throw $e;
            //return false;

        }
        return true;
    }

    public function actionActivePrice()
    {

        return false;

        $searchQuery = "
                    LEFT JOIN front2.b_iblock_element_property bep ON bep.IBLOCK_ELEMENT_ID=b.ID AND bep.IBLOCK_PROPERTY_ID=88
                    LEFT JOIN cms_content_property cp ON cp.vendor_id = bep.IBLOCK_PROPERTY_ID
                    LEFT JOIN cms_content_element rep ON rep.bitrix_id=bep.VALUE AND rep.content_id=(select id from cms_content where code='PRICE_ACTIVE') 
                    LEFT JOIN cms_content_element ce ON ce.bitrix_id = bep.IBLOCK_ELEMENT_ID and ce.content_id=2
                    LEFT JOIN cms_content_element_property exist_prop_value 
                           ON exist_prop_value.element_id=ce.id and exist_prop_value.property_id=cp.id and rep.id = exist_prop_value.value
                    WHERE
                            bep.VALUE is not null
                            and ce.id is not null
                            and bep.IBLOCK_PROPERTY_ID=88
                    ";
        $deleteQuery = "
            DELETE FROM cms_content_element_property 
            WHERE property_id = 82
              AND id NOT IN (
                SELECT id FROM (
                  SELECT exist_prop_value.id FROM front2.b_iblock_element b {$searchQuery} AND exist_prop_value.id IS NOT NULL
                ) mq
              )";
        $insertQuery = "
                INSERT INTO cms_content_element_property (id, created_at, updated_at, property_id, element_id, value)
                  SELECT DISTINCTROW
                    ifnull(exist_prop_value.id, 'default') as current_prop_value,
                    UNIX_TIMESTAMP() as created_at,
                    UNIX_TIMESTAMP() as updated_at,
                        cp.id as property_id,
                        ce.id as element_id,
                        rep.id as value
                    FROM front2.b_iblock_element b
                    {$searchQuery}
                ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at), value=VALUES(value)
        ";

        $this->stdout("Syncing ACTIVE PRICE property ", Console::FG_YELLOW);

        try {

            //$transaction = \Yii::$app->db->beginTransaction();

            $affected = \Yii::$app->db->createCommand($insertQuery)->execute();
            $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

            $affected = \Yii::$app->db->createCommand($deleteQuery)->execute();
            $this->stdout(" done. Deleted " . $affected . "\n", Console::FG_GREEN);

            //$transaction->commit();

        } catch (\yii\db\Exception $e) {

            $this->logError($e);

            //$transaction->rollBack();
            throw $e;
            //return false;

        }

        $searchQuery = "
                    left join cms_content_element parent ON parent.id=e.parent_content_element_id
                    left join cms_content_element_property ep ON ep.element_id=parent.id AND ep.property_id=82
                    left join cms_content_element_property exist_prop_value 
                           ON exist_prop_value.element_id=e.id AND exist_prop_value.property_id=174 AND ep.value = exist_prop_value.value
                    where e.content_id=10
                    ";

        $deleteQuery = "
            DELETE FROM cms_content_element_property 
            WHERE property_id = 174
              AND id NOT IN (
                SELECT id FROM (
                  SELECT exist_prop_value.id FROM cms_content_element e {$searchQuery} AND exist_prop_value.id IS NOT NULL
                ) mq
              )";
        $insertQuery = "
                INSERT INTO cms_content_element_property (id, created_at, updated_at, property_id, element_id, value)
                  SELECT DISTINCTROW 
                        ifnull(exist_prop_value.id, 'default') as current_prop_value,
                        unix_timestamp() as created_at,
                        unix_timestamp() as updated_at,
                        174 as property_id,
                        e.id as element_id,
                        ep.value
                    from cms_content_element e
                    {$searchQuery}
                ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at), value=VALUES(value)
        ";

        $this->stdout("Syncing OFFERS ACTIVE PRICE property ", Console::FG_YELLOW);

        try {

            //$transaction = \Yii::$app->db->beginTransaction();

            $affected = \Yii::$app->db->createCommand($insertQuery)->execute();
            $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

            $affected = \Yii::$app->db->createCommand($deleteQuery)->execute();
            $this->stdout(" done. Deleted " . $affected . "\n", Console::FG_GREEN);

            //$transaction->commit();

        } catch (\yii\db\Exception $e) {

            $this->logError($e);

            //$transaction->rollBack();
            throw $e;
            //return false;

        }

        return true;

    }

    public function actionPricesIndex()
    {

        $this->stdout("Rebuilding aggregated prices table (ss_shop_product_prices)\n", Console::FG_CYAN);

        try {
            $this->fixTableCollation('shop_type_price', 'code', 'utf8_general_ci');
        } catch (\yii\db\Exception $e) {
            $this->stdout("Can't change collation for table cms_content_property\n", Console::FG_RED);
            return false;
        }

        \Yii::$app->db->createCommand("SET sql_mode = '';")->execute();

        $basePriceRow = \Yii::$app->db->createCommand("SELECT id FROM `shop_type_price` WHERE `code` = 'BASE'")->queryOne(); // 4
        $basePriceId = $basePriceRow['id'];

        $sSPriceRow = \Yii::$app->db->createCommand("SELECT id FROM `shop_type_price` WHERE `code` = 'SHOPANDSHOW'")->queryOne(); // 6
        $sSPriceId = $sSPriceRow['id'];

        //  --, IF(max_price > 100, (floor(max_price / 100) * 100) - 1, max_price) AS max_price2
        $query = <<<SQL
INSERT INTO ss_shop_product_prices (product_id, type_price_id, price, min_price, max_price, discount_percent)
    SELECT t.*, ROUND(((max_price - price) / max_price) * 100 ) AS discount_percent
    FROM (
        SELECT product_id, type_price_id, IF(price = 0, min_price, price) AS price, min_price, max_price
        FROM (
        SELECT product.id AS product_id, COALESCE(stp_offer.id, {$basePriceId}) AS type_price_id, 

            CASE WHEN product.content_id = 2 THEN
              COALESCE(NULLIF(MIN(COALESCE(sp_min_offer.price, sp_min_product.price)), ''), COALESCE(spp_offer.price, spp_base.price))
            ELSE 
              COALESCE(spp_offer.price, spp_base.price)
            END AS price,
            
            COALESCE(NULLIF(MIN(COALESCE(sp_min_offer.price, sp_min_product.price)), ''), COALESCE(spp_offer.price, spp_base.price)) AS min_price, 
            MIN(COALESCE(sp_max_offer.price, sp_max_product.price)) AS max_price
              
--             FROM cms_content_element AS product 
--             LEFT JOIN cms_content_element AS child_products ON child_products.parent_content_element_id = product.id AND child_products.active = 'Y'
            
            FROM (
              SELECT product.* FROM cms_content_element AS product
              INNER JOIN shop_product AS sp_product ON sp_product.id = product.id AND sp_product.quantity > 0
              WHERE product.active = 'Y' AND product.content_id IN (2, 5, 10) -- AND product.id = 863319 OR product.parent_content_element_id = 863319  -- Для отладки
            ) AS product 
 
            LEFT JOIN (
               SELECT child_products.* FROM cms_content_element AS child_products 
               INNER JOIN shop_product AS sp_offer ON sp_offer.id = child_products.id AND sp_offer.quantity > 0
               WHERE child_products.active = 'Y' AND child_products.content_id = 10
            ) AS child_products ON child_products.parent_content_element_id = product.id 
            
            
            LEFT JOIN cms_content_element_property AS price_active_id ON price_active_id.element_id = COALESCE(product.parent_content_element_id, product.id)
               AND price_active_id.property_id = (SELECT id FROM cms_content_property WHERE code = 'PRICE_ACTIVE' AND content_id = 2) -- 82
            
            LEFT JOIN cms_content_element_property AS price_active_type ON price_active_type.element_id = price_active_id.value 
                AND price_active_type.property_id = (SELECT id FROM cms_content_property WHERE code = 'PRICE_CODE') -- 130
            
            LEFT JOIN shop_type_price AS stp_offer ON stp_offer.code = COALESCE(price_active_type.value, 'BASE')
        
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


        $this->stdout("Building index ", Console::FG_YELLOW);

        try {

            //$transaction = \Yii::$app->db->beginTransaction();

            $affected = \Yii::$app->db->createCommand($query)->execute();
            $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

            //$transaction->commit();

        } catch (\yii\db\Exception $e) {

            $this->logError($e, $query);

            //$transaction->rollBack();
            throw $e;
            //return false;

        }

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
