<?php

use yii\db\Migration;

class m171009_134404_create_table_statistic extends Migration
{
    private $tableName = "{{%shop_product_statistic}}";

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'k_viewed' => $this->float(),
            'k_ordered' => $this->float(),
            'k_margin' => $this->float(),
            'k_pzp' => $this->float(),
            'k_1' => $this->float(),
            'k_2' => $this->float(),
        ], $tableOptions);

        $this->addForeignKey(
            'shop_product_statistic_shop_product',
            $this->tableName, 'id',
            'shop_product', 'id'
        );

        $this->createIndex('product_id', $this->tableName, 'id', true);
        $this->createIndex('k_1', $this->tableName, 'k_1');
    }

    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
