<?php

use yii\db\Migration;

class m200117_144832_add_segment_bage_columns extends Migration
{

    const TABLE_NAME = '{{%segment}}';

    const COLUMN_NAMES = ['badge','badge_2'];

    public function safeUp()
    {
        foreach (self::COLUMN_NAMES as $column_name) {
            $this->addColumn(self::TABLE_NAME, $column_name, $this->string());
        }
    }

    public function safeDown()
    {
        foreach (self::COLUMN_NAMES as $column_name) {
            $this->dropColumn(self::TABLE_NAME, $column_name);
        }

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200117_144832_add_segment_bage_columns cannot be reverted.\n";

        return false;
    }
    */
}
