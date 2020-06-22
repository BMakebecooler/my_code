<?php

use yii\db\Migration;

class m190912_113710_add_segment_timestamp_columns extends Migration
{
    const TABLE_NAME = '{{%segment}}';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, 'start_timestamp', $this->integer());
        $this->addColumn(self::TABLE_NAME, 'end_timestamp', $this->integer());
    }

    public function safeDown()
    {
        echo "m190912_113710_add_segment_timestamp_columns cannot be reverted.\n";
        $this->dropColumn(self::TABLE_NAME, 'start_timestamp', $this->integer());
        $this->dropColumn(self::TABLE_NAME, 'end_timestamp', $this->integer());

        return true;
    }
}
