<?php

use yii\db\Migration;

class m170901_090410_alter_table_ss_shares_products_drop_column_id extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('ss_shares_products', 'id');
    }

    public function safeDown()
    {
        echo "m170901_090410_alter_table_ss_shares_products_drop_column_id cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170901_090410_alter_table_ss_shares_products_drop_column_id cannot be reverted.\n";

        return false;
    }
    */
}
