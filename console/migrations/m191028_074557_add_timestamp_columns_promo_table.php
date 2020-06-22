<?php

use yii\db\Migration;

class m191028_074557_add_timestamp_columns_promo_table extends Migration
{

    const TABLE_NAME = '{{%promo}}';
    const COLUMN_NAMES = ['start_timestamp','end_timestamp'];

    public function safeUp()
    {
        foreach (self::COLUMN_NAMES as $column_name) {
            $this->addColumn(self::TABLE_NAME, $column_name, $this->integer()->unsigned()->defaultValue(1));
        }
    }

    public function safeDown()
    {
        foreach (self::COLUMN_NAMES as $column_name) {
            $this->dropColumn(self::TABLE_NAME, $column_name);
        }

        return true;
    }
}
