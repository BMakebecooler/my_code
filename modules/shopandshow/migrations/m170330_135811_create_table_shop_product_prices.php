<?php

/**
 * php ./yii migrate/up --migrationPath=@modules/shopandshow/migrations
 */

use yii\db\Migration;

class m170330_135811_create_table_shop_product_prices extends Migration
{

    public function safeUp()
    {

        $tableExist = $this->db->getTableSchema("ss_shop_product_prices", true);

        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('ss_shop_product_prices', [
            'id' => $this->primaryKey(),
            'created_at' => 'timestamp NOT NULL', //->defaultValue(['expression'=>'CURRENT_TIMESTAMP']),
            'updated_at' => 'timestamp NOT NULL', //->defaultValue(['expression'=>'CURRENT_TIMESTAMP']),
            'product_id' => $this->integer()->unique()->notNull(),
            'type_price_id' => $this->integer()->notNull(),
            'price' => $this->money()->notNull(),
            'min_price' => $this->money()->notNull(),
            'max_price' => $this->money()->notNull(),
        ], $tableOptions);

        $this->createIndex('I_product_id', 'ss_shop_product_prices', 'product_id');
        $this->createIndex('I_type_price_id', 'ss_shop_product_prices', 'type_price_id');
        $this->createIndex('I_price', 'ss_shop_product_prices', 'price');

//        $this->createIndex('I_min_price', 'ss_shop_product_prices', 'min_price');
//        $this->createIndex('I_max_price', 'ss_shop_product_prices', 'max_price');

        $this->execute("ALTER TABLE ss_shop_product_prices CHANGE created_at created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP");

        $this->execute("ALTER TABLE ss_shop_product_prices CHANGE updated_at updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP");

        $this->execute("ALTER TABLE ss_shop_product_prices COMMENT = 'Цены для товаров';");

/*        $this->addForeignKey(
            'shop_product_prices_shop_product', 'ss_shop_product_prices',
            'product_id', '{{%cms_content_element}}', 'id', 'SET NULL', 'SET NULL'
        )*/;

//ALTER TABLE ss_shop_product_prices ADD CONSTRAINT Fk_spp_sp_product_id FOREIGN KEY (product_id) REFERENCES cms_content_element(id) -- ON DELETE SET NULL ON UPDATE SET NULL;


/*        ALTER TABLE ss_shop_product_prices
ADD FOREIGN KEY (product_id) REFERENCES cms_content_element (id) ON DELETE RESTRICT ON UPDATE RESTRICT*/

        $this->insertingData();
    }


    public function safeDown()
    {

//        $this->dropForeignKey('shop_product_prices_shop_product', 'ss_shop_product_prices');

        $this->dropTable('ss_shop_product_prices');
    }

    private function insertingData()
    {
        $insertSql = <<<SQL
        
SET sql_mode = '';

DELETE FROM ss_shop_product_prices WHERE 1=1;

SET @base_price_id = (SELECT id FROM shop_type_price WHERE def = 'Y');

INSERT INTO ss_shop_product_prices (product_id, type_price_id, price, min_price, max_price)

    SELECT product.id AS product_id, COALESCE(stp_offer.id, @base_price_id) AS type_price_id, COALESCE(sp_offer.price, sp_base.price) AS price,
    
    MIN(COALESCE(sp_min_max_offer.price, sp_min_max_product.price)) AS min_price, 
    MAX(COALESCE(sp_min_max_offer.price, sp_min_max_product.price)) AS max_price
    
    FROM cms_content_element AS product
    
    LEFT JOIN cms_content_element parent_products ON parent_products.parent_content_element_id = product.id
    
    LEFT JOIN cms_content_element_property price_active_id ON price_active_id.element_id = product.id 
       AND price_active_id.property_id = (SELECT id FROM cms_content_property WHERE code = 'PRICE_ACTIVE' AND content_id = 2)
    
    LEFT JOIN cms_content_element_property price_active_type ON  price_active_type.element_id = price_active_id.value 
        AND price_active_type.property_id = (SELECT id FROM cms_content_property WHERE code = 'PRICE_CODE')
    
    LEFT JOIN shop_type_price AS stp_offer ON stp_offer.code = price_active_type.value

    LEFT JOIN shop_product_price AS sp_offer ON sp_offer.product_id = parent_products.id AND (sp_offer.type_price_id = stp_offer.id OR sp_offer.type_price_id = @base_price_id) 
    LEFT JOIN shop_product_price AS sp_base ON sp_base.type_price_id = @base_price_id AND sp_base.product_id = product.id
    
    LEFT JOIN shop_product_price AS sp_min_max_offer ON sp_min_max_offer.product_id IN (parent_products.id)
    LEFT JOIN shop_product_price AS sp_min_max_product ON sp_min_max_product.product_id = product.id
    
    WHERE product.content_id IN (2, 10)
    
    GROUP BY product.id
SQL;

        return $this->execute($insertSql);
    }
}
