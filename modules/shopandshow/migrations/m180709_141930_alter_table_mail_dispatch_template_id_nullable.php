<?php

use yii\db\Migration;

class m180709_141930_alter_table_mail_dispatch_template_id_nullable extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('ss_mail_dispatch', 'mail_template_id', $this->integer()->null());
    }

    public function safeDown()
    {
        $this->alterColumn('ss_mail_dispatch', 'mail_template_id', $this->integer()->notNull());
    }
}
