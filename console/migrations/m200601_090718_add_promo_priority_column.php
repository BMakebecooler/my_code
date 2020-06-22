<?php

use yii\db\Migration;

class m200601_090718_add_promo_priority_column extends Migration
{
    const TABLE_NAME = '{{%promo}}';
    const COLUMN_NAME = 'priority';
    const COLUMN_NAME_OLD = 'rating';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::COLUMN_NAME, $this->integer()->defaultValue(0));
        $this->createIndex(self::COLUMN_NAME . '_i', self::TABLE_NAME, self::COLUMN_NAME);
        $this->execute("UPDATE " . self::TABLE_NAME . " SET `" . self::COLUMN_NAME . "` = `" . self::COLUMN_NAME_OLD . "`");
    }

    public function safeDown()
    {
        $this->dropIndex(self::COLUMN_NAME . '_i', self::TABLE_NAME);
        $this->dropColumn(self::TABLE_NAME, self::COLUMN_NAME);

    }

}