<?php

use yii\db\Migration;

class m180626_140437_alter_table_shop_product_statistic_add_column_k_stock extends Migration
{
    private $table_name = '{{%shop_product_statistic}}';

    public function safeUp()
    {
        $this->addColumn($this->table_name, 'k_stock', $this->float()->unsigned());

        $this->createIndex('i_k_stock', $this->table_name, 'k_stock');
    }

    public function safeDown()
    {
        $this->dropColumn($this->table_name, 'k_stock');
    }
}
