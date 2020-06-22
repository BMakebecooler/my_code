<?php

use yii\db\Migration;

class m200521_133146_alter_table_setting_add_column_count_brands_products extends Migration
{
    private $tableName = "{{%setting}}";
    private $columnName = "count_brands_products";

    public function safeUp()
    {
        $this->addColumn($this->tableName, $this->columnName, $this->boolean()->defaultValue(\common\helpers\Common::BOOL_Y_INT));
    }

    public function safeDown()
    {
        $this->dropColumn($this->tableName, $this->columnName);
    }
}
