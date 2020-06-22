<?php

use yii\db\Migration;

class m260519_120737_alter_table_emails_users extends Migration
{
    public function safeUp()
    {
        try {
            $this->addColumn('cms_user_email', 'source', $this->string());
            $this->addColumn('cms_user_phone', 'source', $this->string());

            $this->addColumn('cms_user_email', 'source_detail', $this->string());
            $this->addColumn('cms_user_phone', 'source_detail', $this->string());

        } catch (Exception $exception) {
            return true;
        }
    }

    public function safeDown()
    {
        try {
            $this->dropColumn('cms_user_email', 'source');
            $this->dropColumn('cms_user_phone', 'source');

            $this->dropColumn('cms_user_email', 'source_detail');
            $this->dropColumn('cms_user_phone', 'source_detail');

        } catch (Exception $exception) {
            return true;
        }
    }
}
