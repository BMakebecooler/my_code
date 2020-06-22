<?php

use yii\db\Migration;

class m180621_131748_add_table_ss_products_segments extends Migration
{
    private $table_name = '{{%ss_products_segments}}';

    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema($this->table_name, true);

        if ($tableExist) {
            return true;
        }

        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->table_name, [
            'id' => $this->primaryKey(),

            'product_id' => $this->integer()->unsigned(),
            'bitrix_id' => $this->integer()->unsigned(),

            'segment' => $this->string(30),

        ], $tableOptions);

        $this->createIndex('i_product_id', $this->table_name, 'product_id', true);
        $this->createIndex('i_segment', $this->table_name, 'segment');
    }

    public function safeDown()
    {
        $this->dropTable($this->table_name);
    }
}
