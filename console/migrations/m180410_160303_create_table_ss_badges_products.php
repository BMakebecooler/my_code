<?php

use yii\db\Migration;

class m180410_160303_create_table_ss_badges_products extends Migration
{
    private $tableName = "{{%ss_badges_products}}";

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->tableName, [
            'badge_id' => $this->integer(11)->unsigned(),
            'bitrix_id' => $this->integer(11)->unsigned()->null(),
            'product_id' => $this->integer(11)->unsigned()->null(),

        ], $tableOptions);

        $this->createIndex('badge_id_bitrix_id', $this->tableName, ['badge_id', 'bitrix_id'], true);
        $this->createIndex('badge_id', $this->tableName, ['badge_id']);

    }

    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
