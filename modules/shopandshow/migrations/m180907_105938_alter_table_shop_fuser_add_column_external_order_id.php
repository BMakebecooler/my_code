<?php

use yii\db\Migration;

class m180907_105938_alter_table_shop_fuser_add_column_external_order_id extends Migration
{
    private $tableName = "{{%shop_fuser}}";
    private $columnAdd = 'external_order_id';

    public function safeUp()
    {
        $this->addColumn($this->tableName, $this->columnAdd, $this->integer()->unsigned()->comment('ID сессионного заказа (из внешней системы)'));
    }

    public function safeDown()
    {
        $this->dropColumn($this->tableName, $this->columnAdd);
    }
}
