<?php

use yii\db\Migration;

class m170904_134207_alter_table_orders_counters extends Migration
{
    public function safeUp()
    {
        $this->addColumn('shop_order', 'counter_send_queue', $this->smallInteger()->defaultValue(0));
        $this->addColumn('shop_order', 'counter_error_queue', $this->smallInteger()->defaultValue(0));
        $this->addColumn('shop_order', 'last_send_queue_at', $this->dateTime());
    }

    public function safeDown()
    {
        echo "m170904_134207_alter_table_orders_counters cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170904_134207_alter_table_orders_counters cannot be reverted.\n";

        return false;
    }
    */
}
