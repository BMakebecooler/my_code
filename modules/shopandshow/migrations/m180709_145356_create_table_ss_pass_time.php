<?php

use yii\db\Migration;

class m180709_145356_create_table_ss_pass_time extends Migration
{
    private $tableName = 'ss_pass_time';

    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema($this->tableName, true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer(),
            'user_id' => $this->integer(),
            'seconds' => $this->integer()


        ], $tableOptions);

        $this->createIndex('I_created_at', $this->tableName, 'created_at');
        $this->createIndex('I_user_id', $this->tableName, 'user_id');
    }

    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
