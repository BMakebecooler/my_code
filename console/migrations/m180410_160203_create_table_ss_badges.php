<?php

use yii\db\Migration;

class m180410_160203_create_table_ss_badges extends Migration
{
    private $tableName = "{{%ss_badges}}";

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),

            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->integer(),

            'begin_datetime' => $this->integer(11)->unsigned()->null(),
            'end_datetime' => $this->integer(11)->unsigned()->null(),

            'image_id' => $this->integer(11)->unsigned()->null(),
            'image_id_product_card' => $this->integer(11)->unsigned()->null(),

            'name' => $this->string(255)->null(),
            'code' => $this->string(255)->null(),
            'url' => $this->string(1056),
            'active' => $this->string(1),
            'description' => $this->string(255)

        ], $tableOptions);

        $this->createIndex('begin_datetime_active', $this->tableName, ['begin_datetime', 'active']);
        $this->createIndex('begin_datetime_end_datetime', $this->tableName, ['begin_datetime', 'end_datetime', 'active']);

    }

    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
