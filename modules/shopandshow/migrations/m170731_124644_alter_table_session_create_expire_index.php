<?php

use yii\db\Migration;

class m170731_124644_alter_table_session_create_expire_index extends Migration
{
    public function safeUp()
    {
        $this->createIndex('I_expire', '{{%session}}', 'expire');
    }

    public function safeDown()
    {
        $this->dropIndex('I_expire', '{{%session}}');
    }
}
