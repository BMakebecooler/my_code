<?php

use yii\db\Migration;

class m260518_110737_alter_table_shop_order extends Migration
{
    public function safeUp()
    {
        try {
            $this->addColumn('shop_order', 'order_number', $this->string());
        } catch (Exception $exception) {
            return true;
        }
    }

    public function safeDown()
    {
        try {
            $this->dropColumn('shop_order', 'order_number');
        } catch (Exception $exception) {
            return true;
        }
    }
}
