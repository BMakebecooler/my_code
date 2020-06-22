<?php

use yii\db\Migration;

class m180921_110051_alter_table_shop_basket_add_column_kfss_position_id extends Migration
{
    private $tableName = "{{%shop_basket}}";
    private $columnAdd = 'kfss_position_id';

    public function safeUp()
    {
        $this->addColumn($this->tableName, $this->columnAdd, $this->integer()->unsigned()->comment('ID позиции товара в заказе (в КФСС)'));
    }

    public function safeDown()
    {
        $this->dropColumn($this->tableName, $this->columnAdd);
    }
}
