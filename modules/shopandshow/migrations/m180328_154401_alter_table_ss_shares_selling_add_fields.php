<?php

use yii\db\Migration;

class m180328_154401_alter_table_ss_shares_selling_add_fields extends Migration
{

    private $table_name = 'ss_shares_selling';


    public function safeUp()
    {
        $this->addColumn($this->table_name, 'product_id', $this->integer());
        $this->addColumn($this->table_name, 'updated_at', $this->integer()->after('created_at'));
        $this->addColumn($this->table_name, 'order_id', $this->integer());
    }

    public function safeDown()
    {
        $this->dropColumn($this->table_name, 'product_id');
        $this->dropColumn($this->table_name, 'updated_at');
        $this->dropColumn($this->table_name, 'order_id');
    }
}
