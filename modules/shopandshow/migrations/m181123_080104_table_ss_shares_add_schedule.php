<?php

use yii\db\Migration;

class m181123_080104_table_ss_shares_add_schedule extends Migration
{
    private $tableName = 'ss_shares';

    public function safeUp()
    {

        $this->addColumn($this->tableName, 'schedule_tree_id', $this->smallInteger()->defaultValue(null));

    }

    public function safeDown()
    {
      $this->dropColumn($this->tableName, 'schedule_tree_id' );

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181123_080104_table_ss_shares_add_schedule cannot be reverted.\n";

        return false;
    }
    */
}
