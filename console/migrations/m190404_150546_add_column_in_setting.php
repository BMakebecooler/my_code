<?php

use yii\db\Migration;

class m190404_150546_add_column_in_setting extends Migration
{
    const TABLE_SETTING = '{{%setting}}';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_SETTING,'robots', $this->text());

    }

    public function safeDown()
    {
        echo "m190404_150546_add_column_in_setting cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190404_150546_add_column_in_setting cannot be reverted.\n";

        return false;
    }
    */
}
