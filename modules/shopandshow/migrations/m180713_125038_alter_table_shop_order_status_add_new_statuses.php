<?php

use yii\db\Migration;

class m180713_125038_alter_table_shop_order_status_add_new_statuses extends Migration
{
    private $tableName = 'shop_order_status';

    public function safeUp()
    {
        $this->insert($this->tableName, ['code' => 'G', 'name' => 'Проверен', 'description' => 'Заказ прошел проверку', 'priority' => 400, 'color' => '#00b4ff']);
        $this->insert($this->tableName, ['code' => 'R', 'name' => 'Готов к формированию отправлений', 'description' => 'Заказ готов к формированию отправлений', 'priority' => 500, 'color' => '#86608e']);
        $this->insert($this->tableName, ['code' => 'T', 'name' => 'Отправлен', 'description' => 'отправлены посылки', 'priority' => 600, 'color' => '#bdda57']);
    }

    public function safeDown()
    {
        $this->delete($this->tableName, ['code' => ['G', 'R', 'T']]);
    }
}
