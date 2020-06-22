<?php

use yii\db\Migration;

class m190919_114331_add_segment_column extends Migration
{
    const TABLE_NAME = '{{%segment}}';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, 'modification_available_percent', $this->integer());
    }

    public function safeDown()
    {
        echo "m190919_114331_add_segment_column cannot be reverted.\n";

        $this->dropColumn(self::TABLE_NAME, 'modification_available_percent', $this->integer());

        return true;
    }
}
