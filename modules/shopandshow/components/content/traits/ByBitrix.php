<?php

namespace modules\shopandshow\components\content\traits;

use common\helpers\User;
use console\controllers\sync\SyncController;

/**
 * Класс для импорта данных из битрикса
 * включает в себя обновление всей информации на странице - цена, видео, фото, характеристики, аквтинсоть и др. свойства
 * User: koval
 * Date: 23.04.18
 * Time: 14:07
 */
trait ByBitrix
{


    /**
     * Обновить всю инфу из битрикса по 1 элементу
     * @return bool
     */
    public function updateAllInfoByBitrix()
    {
        // Порядок важен
        $this->updateQuantityByBitrix();
        $this->updateActivityByBitrix();

        if(User::isDebug()){
            $this->updatePriceByBitrix();
        }

        $this->updateVideoByBitrix();

        return true;
    }

    /**
     * Обновить кол-во
     * @return bool
     */
    public function updateQuantityByBitrix()
    {

        $queryModifications = "
                update shop_product t1
                inner join (
                    SELECT
                        b.id,
                        ce.bitrix_id,
                        CASE WHEN bc.quantity > 0 THEN bc.quantity ELSE 0 END as front_quantity,
                        ce.id as element_id
                    FROM cms_content_element ce
                    LEFT JOIN front2.b_catalog_product bc ON bc.id = ce.bitrix_id
                    LEFT JOIN front2.b_iblock_element b ON b.id = ce.bitrix_id
                    WHERE
                        ce.content_id in (2, 10) 
                        AND ce.tree_id IS NOT NULL
                        AND b.iblock_id in (10, 11)
                        AND ce.parent_content_element_id = :product_id
                        group by ce.bitrix_id
                ) t2 ON t2.element_id=t1.id
                set t1.quantity=t2.front_quantity
        ";

        $queryProducts = "
                update shop_product t1
                inner join (
                    SELECT
                        sum(CASE WHEN t.quantity > 0 THEN t.quantity ELSE 0 END) as sum_quantity,
                        ce.parent_content_element_id as element_id
                    FROM cms_content_element AS ce, shop_product AS t
                    WHERE
                        ce.content_id = 10
                        AND ce.tree_id IS NOT NULL
                        AND ce.active = 'Y'
                        AND t.id = ce.id
                        AND ce.id = :product_id
                        group by ce.parent_content_element_id
                ) t2 ON t2.element_id=t1.id
                set t1.quantity=t2.sum_quantity
        ";

        $insertTransaction = \Yii::$app->db->beginTransaction();

        try {

            \Yii::$app->db->createCommand($queryModifications, [':product_id' => $this->productId])->execute();

            \Yii::$app->db->createCommand($queryProducts, [':product_id' => $this->productId])->execute();

            $insertTransaction->commit();

        } catch (\yii\db\Exception $e) {

            $insertTransaction->rollBack();

            return false;
        }

        return false;
    }

    /**
     * Обновить активность товара и его предложений
     * @return bool
     */
    public function updateActivityByBitrix()
    {

        $queryProducts = "
                update cms_content_element t1
                inner join (
                    SELECT
                        ce.id as element_id,
                        CASE WHEN b.active='N' THEN 'N' ELSE 'Y' END as visible
                    FROM cms_content_element ce
                    LEFT JOIN front2.b_iblock_element_property bep ON bep.iblock_element_id = ce.bitrix_id and bep.iblock_property_id=94
                    LEFT JOIN front2.b_iblock_property_enum bpe ON bpe.property_id=bep.iblock_property_id AND bpe.id=bep.value_num
                    LEFT JOIN front2.b_iblock_element b ON b.id = ce.bitrix_id
                    where
                        ce.content_id in (2) AND ce.id = :product_id
                ) t2 ON t2.element_id=t1.id
                set t1.active=t2.visible
        ";

        \Yii::$app->db->createCommand($queryProducts, [':product_id' => $this->productId])->execute();


        $queryOffers = "
                update cms_content_element t1
                inner join (
                    SELECT
                        ce.id as element_id,
                        CASE WHEN b.active='N' OR bep.VALUE IS NOT NULL THEN 'N' ELSE 'Y' END as visible
                    FROM cms_content_element ce
                    LEFT JOIN front2.b_iblock_element_property bep ON bep.iblock_element_id = ce.bitrix_id and bep.iblock_property_id=94
                    LEFT JOIN front2.b_iblock_property_enum bpe ON bpe.property_id=bep.iblock_property_id AND bpe.id=bep.value_num
                    LEFT JOIN front2.b_iblock_element b ON b.id = ce.bitrix_id
                    where
                        ce.content_id in (10) AND ce.parent_content_element_id = :product_id
                ) t2 ON t2.element_id=t1.id
                set t1.active=t2.visible
        ";

        \Yii::$app->db->createCommand($queryOffers, [':product_id' => $this->productId])->execute();


        return true;
    }

    /**
     * @return bool
     */
    public function updateVideoByBitrix()
    {
        $query = "
            SELECT
                lp.CODE as local_code, 
                lp.ID as local_prop_id, 
                lp.content_id as local_content_id, 
                lp.property_type as local_type, 
                lp.multiple as local_is_multiple,
                fp.CODE as front_code, 
                fp.ID as front_prop_id, 
                fp.iblock_id as front_iblock_id, 
                fp.PROPERTY_TYPE as front_type, 
                fp.MULTIPLE as front_is_multiple
            FROM cms_content_property lp
            LEFT JOIN front2.b_iblock_property fp ON fp.ID = lp.vendor_id
            WHERE
              lp.code IN ('VIDEO_PRICE_BASE','VIDEO_PRICE_DISCOUNTED','VIDEO_PRICE_TODAY','VIDEO_PRICE_BASE')
              AND fp.ID is NOT NULL
        ";

        $properties = \Yii::$app->db->createCommand($query)->queryAll();

        foreach ($properties as $property) {

            \Yii::$app->db->createCommand('DELETE FROM cms_content_element_property WHERE element_id = :product_id AND property_id = :property_id', [
                ':product_id' => $this->productId,
                ':property_id' => $property['local_prop_id'],
            ])->execute();

            $selectQuery = "
            select distinct
                UNIX_TIMESTAMP(b.date_create) as created_at,
                UNIX_TIMESTAMP(b.timestamp_x) as updated_at,
                {$property['local_prop_id']} as property_id,
                ce.id as element_id,
                CASE WHEN bp.code='NOT_PUBLIC' AND bep.value IS NOT NULL AND bep.value != 'N' THEN 'Y' ELSE bep.VALUE END as value,
                null as value_num,
                null as value_enum
            from front2.b_iblock_element_property bep
            left join front2.b_iblock_element b ON b.id=bep.iblock_element_id AND b.iblock_id={$property['front_iblock_id']}
            left join front2.b_iblock_property bp ON bp.id=bep.iblock_property_id
            left join cms_content_element ce ON ce.bitrix_id=bep.iblock_element_id and ce.content_id={$property['local_content_id']}
            where
                bep.iblock_property_id={$property['front_prop_id']}
                and ce.id = :product_id
                and ce.content_id={$property['local_content_id']}
                and b.iblock_id={$property['front_iblock_id']}
        ";


            $insertQuery = "
            INSERT INTO cms_content_element_property (created_at, updated_at, property_id, element_id, value, value_num, value_enum)
            {$selectQuery}
            ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at), value=VALUES(value), value_num=VALUES(value_num), value_enum=VALUES(value_enum)
        ";

            \Yii::$app->db->createCommand($insertQuery, [':product_id' => $this->productId])->execute();
        }


        return true;
    }


    /**
     * Обновить цену
     * @return bool
     */
    public function updatePriceByBitrix()
    {
        $sql = <<<SQL
DELETE FROM shop_product_price WHERE product_id IN (SELECT id FROM cms_content_element WHERE parent_content_element_id = :product_id OR id =:product_id)
SQL;

        \Yii::$app->db->createCommand($sql, [':product_id' => $this->productId])->execute();


        $fixTableCollation = function ($table, $column = 'code', $collation = 'utf8_unicode_ci') {

            \Yii::$app->db->createCommand("ALTER TABLE {$table} CHARACTER SET utf8 COLLATE {$collation}")->execute();

            $field = \Yii::$app->db->createCommand("SHOW FIELDS FROM {$table} where Field ='{$column}'")->queryOne();

            $field['Type'] = strtoupper($field['Type']);
            $field['Null'] = $field['Null'] == 'NO' ? 'NOT NULL' : '';

            \Yii::$app->db->createCommand("ALTER TABLE {$table} CHANGE {$column} {$column} {$field['Type']} CHARACTER SET utf8 COLLATE {$collation} {$field['Null']}")->execute();

        };

        $fixTableCollation('shop_type_price');


        $typesPrices = function () {
            $query = "
            select
                stp.id as type_price_id,
                stp.code as price_code,
                ibp.id as bitrix_property_id,
                ibp.code bitrix_price_code,
                CASE WHEN ibp.iblock_id = 10 THEN 2 ELSE
                 CASE WHEN ibp.iblock_id= 11 THEN 10 END END as content_id
            from shop_type_price AS stp
            left join front2.b_iblock_property AS ibp ON ibp.code=CONCAT('PRICE_',stp.code)
        ";

            return \Yii::$app->db->createCommand($query)->queryAll();
        };

        $query = "
            insert IGNORE into shop_product_price (created_at, updated_at, product_id, type_price_id, price, currency_code)
            select
                unix_timestamp() as created_at,
                unix_timestamp() as updated_at,
                p.id AS product_id,
                :TYPE_PRICE_ID as type_price_id,
                ifnull(bp.value_num, 0) as price,
                'RUB' as currency_code
            from shop_product p
            left join cms_content_element e ON e.id = p.id
            left join front2.b_iblock_element b ON b.id=e.bitrix_id
            left join front2.b_iblock_element_property bp ON bp.iblock_element_id=b.id AND bp.iblock_property_id=:BITRIX_PROPERTY_ID
            where p.id = :PRODUCT_ID OR e.parent_content_element_id = :PRODUCT_ID 
            GROUP BY p.id; 
        ";

        $typePrices = $typesPrices();

        var_dump($typePrices);

        foreach ($typePrices as $typesPrice) {

            if ($typesPrice['price_code'] == 'SHOPANDSHOW') { //$typesPrice['content_id'] != SyncController::LOCAL_PRODUCT_BLOCK_ID ||
                continue;
            }

            $params = [
                ':TYPE_PRICE_ID' => (int)$typesPrice['type_price_id'],
                ':BITRIX_PROPERTY_ID' => (int)$typesPrice['bitrix_property_id'],
                ':PRODUCT_ID' => (int)$this->productId,
            ];

            $result = \Yii::$app->db->createCommand($query, $params)->execute();

            var_dump($typesPrice['type_price_id']);
            var_dump($typesPrice['bitrix_property_id']);
            var_dump($result);
        }


        $activePricePropId = \Yii::$app->db->createCommand('SELECT id,code FROM `cms_content_property` WHERE `code` = "PRICE_ACTIVE" and `content_id` IN (2,10)')->queryAll();
        foreach ($activePricePropId as $prop) {
            \Yii::$app->db->createCommand('DELETE FROM cms_content_element_property WHERE property_id= :property_id AND element_id =:element_id', [
                ':property_id' => $prop['id'],
                ':element_id' => $this->productId,
            ])->execute();
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
                            AND b.id = :bitrix_id
                ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at), value=VALUES(value)
        ";


        \Yii::$app->db->createCommand($query, [':bitrix_id' => $this->bitrixId])->execute();

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
                    where e.content_id=10 AND e.parent_content_element_id =:product_id
                ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at), value=VALUES(value)
        ";

        \Yii::$app->db->createCommand($query, [':product_id' => $this->productId])->execute();


        //Расчет цены ШШ
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
                    p.type_price_id=4 AND (p.product_id = :product_id OR e.parent_content_element_id =:product_id)
            on duplicate key update price=VALUES(price), updated_at=CURRENT_TIMESTAMP
              
        ";

        \Yii::$app->db->createCommand($query, [':product_id' => $this->productId])->execute();





        $fixTableCollation('shop_type_price', 'code', 'utf8_general_ci');

        \Yii::$app->db->createCommand("SET sql_mode = '';")->execute();

        $basePriceRow = \Yii::$app->db->createCommand("SELECT id FROM `shop_type_price` WHERE `code` = 'BASE'")->queryOne(); // 4
        $basePriceId = $basePriceRow['id'];

        $sSPriceRow = \Yii::$app->db->createCommand("SELECT id FROM `shop_type_price` WHERE `code` = 'SHOPANDSHOW'")->queryOne(); // 6
        $sSPriceRow = $sSPriceRow['id'];

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
              
            FROM (
              SELECT product.* FROM cms_content_element AS product
              INNER JOIN shop_product AS sp_product ON sp_product.id = product.id AND sp_product.quantity > 0
              WHERE product.active = 'Y' AND product.content_id IN (2, 10)  AND product.id = :product_id OR product.parent_content_element_id = :product_id 
            ) AS product 
 
            LEFT JOIN (
               SELECT child_products.* FROM cms_content_element AS child_products 
               INNER JOIN shop_product AS sp_offer ON sp_offer.id = child_products.id AND sp_offer.quantity > 0
               WHERE child_products.active = 'Y' AND child_products.content_id = 10  AND child_products.parent_content_element_id = :product_id 
            ) AS child_products ON child_products.parent_content_element_id = product.id 
            
            
            LEFT JOIN cms_content_element_property AS price_active_id ON price_active_id.element_id = COALESCE(product.parent_content_element_id, product.id)
               AND price_active_id.property_id = (SELECT id FROM cms_content_property WHERE code = 'PRICE_ACTIVE' AND content_id = 2) -- 82
            
            LEFT JOIN cms_content_element_property AS price_active_type ON price_active_type.element_id = price_active_id.value 
                AND price_active_type.property_id = (SELECT id FROM cms_content_property WHERE code = 'PRICE_CODE') -- 130
            
            LEFT JOIN shop_type_price AS stp_offer ON stp_offer.code = price_active_type.value
        
            LEFT JOIN shop_product_price AS spp_offer ON spp_offer.product_id = product.id AND (spp_offer.type_price_id = stp_offer.id)
            LEFT JOIN shop_product_price AS spp_base ON spp_base.type_price_id = {$basePriceId} AND spp_base.product_id = product.id
            
            -- Минимальная цена товаров и предложений
            LEFT JOIN shop_product_price AS sp_min_offer ON sp_min_offer.product_id IN (child_products.id) AND sp_min_offer.price > 0  
                  AND (sp_min_offer.type_price_id = stp_offer.id OR sp_min_offer.type_price_id = {$basePriceId})
            LEFT JOIN shop_product_price AS sp_min_product ON sp_min_product.product_id = product.id AND sp_min_product.price > 0 
                  AND (sp_min_product.type_price_id = stp_offer.id OR sp_min_product.type_price_id = {$basePriceId})
            
            -- Максимальная цена товаров и предложений от типа цена ШШ
            LEFT JOIN shop_product_price AS sp_max_offer ON sp_max_offer.product_id IN (child_products.id) AND sp_max_offer.price > 0  
                  AND (sp_max_offer.type_price_id = {$sSPriceRow})
            LEFT JOIN shop_product_price AS sp_max_product ON sp_max_product.product_id = product.id AND sp_max_product.price > 0 
                  AND sp_max_product.type_price_id = {$sSPriceRow}
   
            GROUP BY product.id
        ) AS t
    ) AS t
ON DUPLICATE KEY UPDATE type_price_id=VALUES(type_price_id),  price=VALUES(price),  min_price=VALUES(min_price),  max_price=VALUES(max_price),  discount_percent=VALUES(discount_percent)
SQL;


        \Yii::$app->db->createCommand($query, [':product_id' => $this->productId])->execute();


//        \Yii::$app->shares->removeThumbs(); //Перегенерируем баннеры

        return true;
    }
}