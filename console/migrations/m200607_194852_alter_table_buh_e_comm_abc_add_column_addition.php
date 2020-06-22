<?php

use yii\db\Migration;

class m200607_194852_alter_table_buh_e_comm_abc_add_column_addition extends Migration
{
    private $tableName = "{{%buh_e_comm_abc}}";
    private $columnName = "addition";

    public function safeUp()
    {
        $this->addColumn($this->tableName, $this->columnName, $this->text()->comment('Поле для доп. данных'));
    }

    public function safeDown()
    {
        $this->dropColumn($this->tableName, $this->columnName);
    }
}
