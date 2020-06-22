<?php

use yii\db\Migration;

class m180416_112056_create_table_ss_task extends Migration
{
    private $table_name = '{{%ss_task}}';

    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema($this->table_name, true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->table_name, [
            'id'                    => $this->primaryKey(),

            'created_by'            => $this->integer(),
            'updated_by'            => $this->integer(),

            'created_at'            => $this->integer(),
            'updated_at'            => $this->integer(),

            'status'                => $this->integer(2),

            'component'             => $this->string(255)->notNull(),
            'component_settings'    => $this->text(),

        ], $tableOptions);

        $this->createIndex('I_status', $this->table_name, 'status');
    }

    public function safeDown()
    {
        $this->dropTable($this->table_name);
    }
}
