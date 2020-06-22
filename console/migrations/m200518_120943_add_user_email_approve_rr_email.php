<?php

use yii\db\Migration;

class m200518_120943_add_user_email_approve_rr_email extends Migration
{

    const TABLE_NAME = '{{%cms_user_email}}';
    const COLUMN_NAME = 'approved_rr';
    const COLUMN_VALUE_NAME = 'value';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::COLUMN_NAME, $this->boolean()->defaultValue(0));
        $this->createIndex(self::COLUMN_NAME . '_i', self::TABLE_NAME, self::COLUMN_NAME);
        $this->createIndex(self::COLUMN_VALUE_NAME . '_i', self::TABLE_NAME, self::COLUMN_VALUE_NAME);
    }

    public function safeDown()
    {
//        echo "m200518_120943_add_user_email_approved_rr_email cannot be reverted.\n";
        $this->dropIndex(self::COLUMN_NAME . '_i', self::TABLE_NAME);
        $this->dropColumn(self::TABLE_NAME, self::COLUMN_NAME);
        return true;
    }

}