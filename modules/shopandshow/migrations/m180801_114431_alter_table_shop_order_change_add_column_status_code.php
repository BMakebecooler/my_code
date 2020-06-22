<?php

use yii\db\Migration;

class m180801_114431_alter_table_shop_order_change_add_column_status_code extends Migration
{
    private $tableName = '{{%shop_order_change}}';
    private $tblShopOrderStatus = '{{%shop_order_status}}';

    public function safeUp()
    {
        $this->addColumn($this->tableName, 'status_code', $this->string(1));
        $this->createIndex('k_status_code', $this->tableName, 'status_code');
        $this->addForeignKey('fk_status_code', $this->tableName, 'status_code', $this->tblShopOrderStatus, 'code', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_status_code', $this->tableName);
        $this->dropIndex('k_status_code', $this->tableName);
        $this->dropColumn($this->tableName, 'status_code');
    }
}
