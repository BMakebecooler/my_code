<?php

use yii\db\Migration;

class m190401_104411_add_table_buh_ecomm_abc extends Migration
{
    const TABLE_BUH_E_COMM_ABC = '{{%buh_e_comm_abc}}';

    public function safeUp()
    {
        $this->createTable(self::TABLE_BUH_E_COMM_ABC,[
            'id' => $this->primaryKey(),
            'guid' => $this->string(),
            'order' => $this->integer()
        ]);

    }

    public function safeDown()
    {
        echo "m190401_104411_add_table_buh_ecomm_abc cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190401_104411_add_table_buh_ecomm_abc cannot be reverted.\n";

        return false;
    }
    */
}
