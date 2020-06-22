<?php

use yii\db\Migration;

class m180227_131109_alter_table_ss_shop_fuser_discount_free_delivery extends Migration
{
    private $tableName = '{{%ss_shop_fuser_discount}}';

    public function safeUp()
    {
        $this->dropColumn($this->tableName, 'free_delivery');
        $this->addColumn($this->tableName, 'free_delivery_discount_id', $this->integer());

        $this->addForeignKey(
            'fk_free_delivery_discount_id',
            $this->tableName, 'free_delivery_discount_id',
            '{{%shop_discount}}', 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_free_delivery_discount_id', $this->tableName);
        $this->dropColumn($this->tableName, 'free_delivery_discount_id');

        $this->addColumn($this->tableName, 'free_delivery', $this->char(1)->defaultValue('N'));
    }
}
