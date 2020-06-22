<?php

use yii\db\Migration;

class m191011_130557_add_segment_price_modification_column extends Migration
{
    const TABLE_SEGMENT = '{{%segment}}';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_SEGMENT, 'calc_price_modifications', $this->boolean()->defaultValue(0));
    }

    public function safeDown()
    {
        echo "m190812_102532_add_column_name_segment cannot be reverted.\n";

        $this->dropColumn(self::TABLE_SEGMENT, 'calc_price_modifications', $this->boolean());

        return true;
    }
}
