<?php

use yii\db\Migration;

class m190812_102532_add_column_name_segment extends Migration
{
    const TABLE_SEGMENT = '{{%segment}}';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_SEGMENT, 'name_lot', $this->string());
    }

    public function safeDown()
    {
        echo "m190812_102532_add_column_name_segment cannot be reverted.\n";

        $this->dropColumn(self::TABLE_SEGMENT, 'name_lot', $this->string());

        return true;
    }


}