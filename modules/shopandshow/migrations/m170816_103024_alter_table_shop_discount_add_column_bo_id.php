<?php

use yii\db\Migration;

class m170816_103024_alter_table_shop_discount_add_column_bo_id extends Migration
{
    public function safeUp()
    {
        $this->addColumn('shop_discount', 'bo_id', $this->integer());
    }

    public function safeDown()
    {
        $this->dropColumn('shop_discount', 'bo_id');
    }
}
