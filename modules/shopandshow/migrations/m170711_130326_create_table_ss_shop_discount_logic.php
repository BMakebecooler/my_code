<?php

use yii\db\Migration;

class m170711_130326_create_table_ss_shop_discount_logic extends Migration
{
    private $tableName = "{{%ss_shop_discount_logic}}";

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        // типы акций и условия
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'shop_discount_id' => $this->integer()->notNull(),
            'logic_type' => $this->char(1)->notNull(),
            'value' => $this->money()->notNull(),
            'discount_type' => $this->char(1)->notNull(),
            'discount_value' => $this->money()->notNull(),
        ], $tableOptions);

        $this->createIndex('I_shop_discount_id', $this->tableName, 'shop_discount_id');

        $this->addForeignKey(
            'ss_shop_discount_logic_shop_discount',
            $this->tableName, 'shop_discount_id',
            '{{%shop_discount}}', 'id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }

}
