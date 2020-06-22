<?php

use yii\db\Migration;

class m190814_083716_alter_table_setting_add_column_onair_youtube_code extends Migration
{
    private $tableName = "{{%setting}}";
    private $columnName = 'onair_youtube_code';

    public function safeUp()
    {
        $this->addColumn($this->tableName, $this->columnName, $this->string());
        return true;
    }

    public function safeDown()
    {
        $this->dropColumn($this->tableName, $this->columnName, $this->string());
        return true;
    }
}
