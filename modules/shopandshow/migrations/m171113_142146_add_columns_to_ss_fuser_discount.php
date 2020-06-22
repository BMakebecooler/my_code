<?php

use yii\db\Migration;

/**
 * Class m171113_142146_add_columns_to_ss_fuser_discount
 */
class m171113_142146_add_columns_to_ss_fuser_discount extends Migration
{
    private $tableName = 'ss_shop_fuser_discount';
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn($this->tableName, 'shop_fuser_id', $this->integer()->null());
        $this->addColumn($this->tableName, 'shop_order_id', $this->integer());

        $this->createIndex('I_shop_order_id', $this->tableName, 'shop_order_id');
        $this->addForeignKey('FK_shop_order_id', $this->tableName, 'shop_order_id', 'shop_order', 'id', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->alterColumn($this->tableName, 'shop_fuser_id', $this->integer()->notNull());
        $this->dropColumn($this->tableName, 'shop_order_id');

        $this->dropForeignKey('FK_shop_order_id', $this->tableName);
        $this->dropIndex('I_shop_order_id', $this->tableName);
    }
}
