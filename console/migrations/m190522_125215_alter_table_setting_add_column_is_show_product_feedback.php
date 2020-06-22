<?php

use yii\db\Migration;

class m190522_125215_alter_table_setting_add_column_is_show_product_feedback extends Migration
{
    private $tableName = '{{%setting}}';
    private $columnName = 'is_show_product_feedback';

    public function safeUp()
    {
        echo "Adding column '{$this->columnName}' in table '{$this->tableName}'" . PHP_EOL;
        $this->addColumn($this->tableName, $this->columnName, $this->boolean()->defaultValue(0));
        echo 'Done' . PHP_EOL;

        return true;
    }

    public function safeDown()
    {
        echo "Remove column '{$this->columnName}' from table '{$this->tableName}'" . PHP_EOL;
        $this->dropColumn($this->tableName, $this->columnName);
        echo 'Done' . PHP_EOL;

        return true;
    }
}
