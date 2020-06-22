<?php

use yii\db\Migration;

class m181030_181259_alter_table_ss_shares extends Migration
{

    private $tableName = 'ss_shares';
    private $tableNameProducts = 'ss_shares_products';


    public function safeUp()
    {
        $this->addColumn($this->tableName, 'is_hidden_catalog', $this->smallInteger()->defaultValue(0));
        $this->addColumn($this->tableNameProducts, 'is_hidden_catalog', $this->smallInteger()->defaultValue(0));

        $this->createIndex('I_is_hidden_catalog', $this->tableNameProducts, 'is_hidden_catalog');
        $this->createIndex('I_product_id', $this->tableNameProducts, 'product_id');

        $sql = <<<SQL
DELETE FROM ss_shares_products WHERE banner_id IN (
    SELECT banner_id FROM (
        SELECT DISTINCT banner_id 
        FROM ss_shares_products AS sp 
        LEFT JOIN ss_shares AS s ON s.id = sp.banner_id 
        WHERE s.id IS NULL
    ) AS t
)
SQL;

        $this->execute($sql);

        $this->addForeignKey('fk_id_banner_id', $this->tableNameProducts, 'banner_id', $this->tableName, 'id', 'CASCADE', 'CASCADE');

        return true;
    }

    public function safeDown()
    {
        $this->dropIndex('I_is_hidden_catalog', $this->tableNameProducts);
        $this->dropIndex('I_product_id', $this->tableNameProducts);

        $this->dropForeignKey('fk_id_banner_id', $this->tableNameProducts);

        $this->dropColumn($this->tableName, 'is_hidden_catalog');
        $this->dropColumn($this->tableNameProducts, 'is_hidden_catalog');

        return true;
    }

}
