<?php

use yii\db\Migration;

class m190710_150048_alter_table_cms_user_email_add_column_value_type extends Migration
{
    private $tableName = "{{%cms_user_email}}";
    private $columnName = "value_type";

    public function safeUp()
    {
        $this->addColumn($this->tableName, $this->columnName, $this->boolean()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn($this->tableName, $this->columnName);
        return true;
    }
}
