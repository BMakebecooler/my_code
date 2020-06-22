<?php

use yii\db\Migration;

class m190125_065213_add_field_shop_order extends Migration
{
    private $tableName = '{{%shop_order}}';

    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->addColumn($this->tableName, 'do_not_need_confirm_call', $this->boolean()->defaultValue(0));
    }

    public function down()
    {
        $this->dropColumn($this->tableName, 'do_not_need_confirm_call');
    }
}
