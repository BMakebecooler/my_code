<?php

use yii\db\Migration;

class m191205_100154_add_segment_products_qty_column extends Migration
{
    const TABLE_NAME = '{{%segment_products}}';
    const COLUMN_NAME = 'qty';

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
