<?php

use yii\db\Migration;

class m180628_112415_alter_table_queue_log_add_field_guid extends Migration
{
    private $tableName = 'queue_log';

    public function safeUp()
    {
        $this->addColumn($this->tableName, 'guid', $this->string(64));

        $this->createIndex('I_guid', $this->tableName, 'guid');
    }

    public function safeDown()
    {
        $this->dropIndex('I_guid', $this->tableName);

        $this->dropColumn($this->tableName, 'guid');
    }

}
