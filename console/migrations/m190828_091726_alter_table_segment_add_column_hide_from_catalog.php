<?php

use yii\db\Migration;

class m190828_091726_alter_table_segment_add_column_hide_from_catalog extends Migration
{
    const TABLE_NAME = '{{%segment}}';
    const COLUMN_NAME = 'hide_from_catalog';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::COLUMN_NAME, $this->boolean()->defaultValue(0));
        $this->createIndex(self::COLUMN_NAME.'_i', self::TABLE_NAME, self::COLUMN_NAME);
    }

    public function safeDown()
    {
        $this->dropIndex(self::COLUMN_NAME.'_i', self::TABLE_NAME);
        $this->dropColumn(self::TABLE_NAME, self::COLUMN_NAME);
    }
}
