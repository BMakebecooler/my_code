<?php

use yii\db\Migration;

class m190208_081709_add_order_payment_number_to_shop_order extends Migration
{
    private $tableName = '{{%shop_order}}';

    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->addColumn($this->tableName, 'order_payment_number', $this->string());
    }

    public function down()
    {
        $this->dropColumn($this->tableName, 'order_payment_number');
    }
}
