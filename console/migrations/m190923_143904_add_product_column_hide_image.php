<?php

use yii\db\Migration;

class m190923_143904_add_product_column_hide_image extends Migration
{
    const TABLE_NAME = '{{%cms_content_element}}';
    const COLUMN_NAME = 'hide_from_catalog_image';

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