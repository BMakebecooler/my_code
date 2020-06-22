<?php

use yii\db\Migration;

class m180719_113404_alter_table_shop_order_status_add_status_delayed extends Migration
{
    private $tableName = 'shop_order_status';

    public function safeUp()
    {
        $this->insert($this->tableName, ['code' => 'D', 'name' => 'Редактируется', 'description' => 'Заказ еще формируется на стороне клиента', 'priority' => 300, 'color' => '#ffd7b5']);
    }

    public function safeDown()
    {
        $this->delete($this->tableName, ['code' => 'D']);
    }
}
