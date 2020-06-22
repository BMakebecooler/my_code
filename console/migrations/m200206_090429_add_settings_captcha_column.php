<?php

use yii\db\Migration;

class m200206_090429_add_settings_captcha_column extends Migration
{
    const TABLE_NAME = '{{%setting}}';
    const COLUMN_NAME= '{{%use_captcha}}';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::COLUMN_NAME, $this->boolean()->defaultValue(1));
    }

    public function safeDown()
    {
        echo "m200206_090429_add_settings_captcha_column cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200206_090429_add_settings_captcha_column cannot be reverted.\n";

        return false;
    }
    */
}
