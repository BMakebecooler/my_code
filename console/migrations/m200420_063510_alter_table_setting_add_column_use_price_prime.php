<?php

use yii\db\Migration;

class m200420_063510_alter_table_setting_add_column_use_price_prime extends Migration
{
    private $tableName = "{{%setting}}";
    private $columnName = 'use_price_prime';

    public function safeUp()
    {
        $this->addColumn($this->tableName, $this->columnName, $this->boolean()->defaultValue(0));
        return true;
    }

    public function safeDown()
    {
        $this->dropColumn($this->tableName, $this->columnName);
        return true;
    }
}
