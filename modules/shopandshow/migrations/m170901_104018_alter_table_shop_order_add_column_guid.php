<?php

use yii\db\Migration;

class m170901_104018_alter_table_shop_order_add_column_guid extends Migration
{
    public function safeUp()
    {
        $this->addColumn('shop_order', 'guid', $this->string(32));
        $this->createIndex('I_guid', 'shop_order', 'guid', true);
    }

    public function safeDown()
    {
        $this->dropIndex('I_guid', 'shop_order');
        $this->dropColumn('shop_order', 'guid');
    }
}
