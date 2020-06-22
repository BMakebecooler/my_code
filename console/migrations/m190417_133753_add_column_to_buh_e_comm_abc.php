<?php

use yii\db\Migration;

class m190417_133753_add_column_to_buh_e_comm_abc extends Migration
{
    const TABLE_BUH_E_COMM_ABC = '{{%buh_e_comm_abc}}';

    public function safeUp()
    {

        $this->addColumn(self::TABLE_BUH_E_COMM_ABC, 'code', $this->string());
    }

    public function safeDown()
    {
        echo "m190417_133753_add_column_to_buh_e_comm_abc cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190417_133753_add_column_to_buh_e_comm_abc cannot be reverted.\n";

        return false;
    }
    */
}
