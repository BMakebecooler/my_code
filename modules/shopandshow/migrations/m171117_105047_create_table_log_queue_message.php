<?php

use yii\db\Migration;

/**
 * Class m171117_105047_create_table_log_queue_message
 */
class m171117_105047_create_table_log_queue_message extends Migration
{
    private $tableName = 'queue_log';
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->tableName, [
            'id' => $this->bigPrimaryKey(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'component' => $this->string(64),
            'exchange_name' => $this->string(64),
            'queue_name' => $this->string(64),
            'routing_key' => $this->string(64),
            'job_class' => $this->string(64),
            'status' => $this->string(2),
            'message' => $this->text(),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }

}
