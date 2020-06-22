<?php

use yii\db\Migration;

class m190823_111429_table_seo_add_indexes extends Migration
{
    const TABLE_NAME = "{{%seo}}";
    const COLUMN_OWNER = 'owner';
    const COLUMN_OWNER_ID = 'owner_id';

    public function safeUp()
    {
        $this->createIndex(self::COLUMN_OWNER . '_i', self::TABLE_NAME, self::COLUMN_OWNER);
        $this->createIndex(self::COLUMN_OWNER_ID . '_i', self::TABLE_NAME, self::COLUMN_OWNER_ID);
    }

    public function safeDown()
    {
        $this->dropIndex(self::COLUMN_OWNER . '_i', self::TABLE_NAME);
        $this->dropIndex(self::COLUMN_OWNER_ID . '_i', self::TABLE_NAME);
    }
}
