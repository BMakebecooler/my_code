<?php

use yii\db\Migration;

class m191031_125157_add_tree_popularity_column extends Migration
{
    const TABLE_NAME = '{{%cms_tree}}';
    const COLUMN_NAME = 'popularity';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::COLUMN_NAME, $this->integer()->defaultValue(1));
        $this->createIndex(self::COLUMN_NAME.'_i', self::TABLE_NAME, self::COLUMN_NAME);
    }

    public function safeDown()
    {
        $this->dropIndex(self::COLUMN_NAME.'_i', self::TABLE_NAME);
        $this->dropColumn(self::TABLE_NAME, self::COLUMN_NAME);
    }

}
