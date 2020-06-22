<?php

use yii\db\Migration;

class m200521_103925_alter_table_setting_add_column_use_filters extends Migration
{
    private $tableName = "{{%setting}}";
    private $columnName = "use_filters";

    public function safeUp()
    {
        $this->addColumn($this->tableName, $this->columnName, $this->boolean()->defaultValue(\common\helpers\Common::BOOL_Y_INT));
    }

    public function safeDown()
    {
        $this->dropColumn($this->tableName, $this->columnName);
    }
}
