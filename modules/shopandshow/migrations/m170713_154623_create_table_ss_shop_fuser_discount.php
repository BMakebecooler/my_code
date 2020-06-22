<?php

use yii\db\Migration;

class m170713_154623_create_table_ss_shop_fuser_discount extends Migration
{
    private $tableName = "{{%ss_shop_fuser_discount}}";

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        // типы акций и условия
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'shop_fuser_id' => $this->integer()->notNull(),
            'discount_name' => $this->string(),
            'discount_price' => $this->money(),
        ], $tableOptions);

        $this->createIndex('I_shop_fuser_id', $this->tableName, 'shop_fuser_id');

        $this->addForeignKey(
            'ss_shop_fuser_discount_shop_fuser',
            $this->tableName, 'shop_fuser_id',
            '{{%shop_fuser}}', 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
