<?php

use yii\db\Migration;

class m171101_082314_create_table_ss_share_schedule extends Migration
{
    private $tableName = '{{%ss_shares_schedule}}';

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_at' => $this->integer(),
            'updated_by' => $this->integer(),
            'begin_datetime' => $this->integer()->notNull(),
            'end_datetime' => $this->integer()->notNull(),
            'block_type' => $this->string()->notNull(),
            'block_position' => $this->integer()->notNull(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
