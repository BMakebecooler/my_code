<?php

use yii\db\Migration;

class m190807_092338_alter_table_setting_add_column_is_op_allowed extends Migration
{
    private $tableName = "{{%setting}}";
    private $columnName = "is_online_payment_allowed";

    public function safeUp()
    {
        $this->addColumn($this->tableName, $this->columnName, $this->boolean()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn($this->tableName, $this->columnName);
    }
}
