<?php

use yii\db\Migration;

class m191225_120919_add_new_param_types_columns extends Migration
{
    const TABLE_NAME = '{{%product_param_type}}';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, 'sort', $this->integer()->defaultValue(0));
        $this->addColumn(self::TABLE_NAME, 'active', $this->boolean()->defaultValue(1));
    }

    public function safeDown()
    {
        echo "m191225_120919_add_new_param_types_columns cannot be reverted.\n";

        $this->dropColumn(self::TABLE_NAME, 'sort');
        $this->dropColumn(self::TABLE_NAME, 'active');

        return false;
    }

}
