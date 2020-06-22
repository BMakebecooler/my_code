<?php

use yii\db\Migration;

class m190227_061316_add_pay_count_to_shop_order extends Migration
{
    private $tableName = '{{%shop_order}}';

    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->addColumn($this->tableName, 'count_payment', $this->integer(32));
    }

    public function down()
    {
        $this->dropColumn($this->tableName, 'count_payment');
    }
}
