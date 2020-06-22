<?php

use yii\db\Migration;

class m191206_113020_add_product_param_cansale_count_column extends Migration
{
    const TABLE_NAME = '{{%product_param}}';
    const COLUMN_NAME = 'count_can_sale';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::COLUMN_NAME, $this->integer()->defaultValue(0));
        $this->createIndex(self::COLUMN_NAME.'_i', self::TABLE_NAME, self::COLUMN_NAME);
    }

    public function safeDown()
    {
        $this->dropIndex(self::COLUMN_NAME.'_i', self::TABLE_NAME);
        $this->dropColumn(self::TABLE_NAME, self::COLUMN_NAME);
    }
}
