<?php

use yii\db\Migration;

class m190419_110223_add_type_for_buh_ecomm extends Migration
{
    const TABLE_BUH_E_COMM_ABC = '{{%buh_e_comm_abc}}';

    public function safeUp()
    {

        $this->addColumn(self::TABLE_BUH_E_COMM_ABC, 'type_id', $this->integer());
    }

    public function safeDown()
    {
        $this->dropColumn(self::TABLE_BUH_E_COMM_ABC, 'type_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190419_110223_add_type_for_buh_ecomm cannot be reverted.\n";

        return false;
    }
    */
}
