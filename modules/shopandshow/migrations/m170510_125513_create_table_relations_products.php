<?php

use yii\db\Migration;

class m170510_125513_create_table_relations_products extends Migration
{
    private $tableNameBannerProducts = "{{%ss_shares_products}}";

    public function safeUp()
    {

        $tableExist = $this->db->getTableSchema($this->tableNameBannerProducts, true);

        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->tableNameBannerProducts, [
            'id' => $this->primaryKey(),
            'banner_id' => $this->integer()->notNull(),
            'bitrix_id' => $this->integer()->notNull(),
            'product_id' => $this->integer(), //
        ], $tableOptions);

        $this->createIndex('banner_id', $this->tableNameBannerProducts, 'banner_id');

        $this->createIndex('banner_id_bitrix_id', $this->tableNameBannerProducts, ['banner_id', 'bitrix_id'], true);

    }

    public function safeDown()
    {
        $this->dropTable($this->tableNameBannerProducts);
    }
}
