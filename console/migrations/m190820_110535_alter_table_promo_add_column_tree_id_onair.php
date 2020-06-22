<?php

use yii\db\Migration;

class m190820_110535_alter_table_promo_add_column_tree_id_onair extends Migration
{
    private $tableName = '{{%promo}}';
    private $columnName = 'tree_id_onair';

    public function safeUp()
    {
        $this->addColumn($this->tableName, $this->columnName, $this->integer()->unsigned());
    }

    public function safeDown()
    {
        $this->dropColumn($this->tableName, $this->columnName);
    }
}
