<?php

use yii\db\Migration;

class m200430_115217_add_user_email_ip_column extends Migration
{

    const TABLE_NAME = '{{%cms_user_email}}';
    const COLUMN_NAME = 'ip';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::COLUMN_NAME, $this->string());
    }

    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, self::COLUMN_NAME);
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200430_115217_add_user_email_ip_column cannot be reverted.\n";

        return false;
    }
    */
}