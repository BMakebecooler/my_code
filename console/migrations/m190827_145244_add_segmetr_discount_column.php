<?php

use yii\db\Migration;

class m190827_145244_add_segmetr_discount_column extends Migration
{

    const TABLE_SEGMENT = '{{%segment}}';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_SEGMENT, 'only_discount', $this->boolean()->defaultValue(0));
    }

    public function safeDown()
    {
        echo "m190827_145244_add_segmetr_discount_column cannot be reverted.\n";

        $this->dropColumn(self::TABLE_SEGMENT, 'only_discount', $this->boolean()->defaultValue(0));

        return true;
    }

}
