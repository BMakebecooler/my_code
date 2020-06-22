<?php

use yii\db\Migration;

class m171004_125016_alter_table_ss_mail_template_name extends Migration
{
    public function safeUp()
    {
        $this->execute('ALTER TABLE ss_mail_template MODIFY name VARBINARY(255)');
    }

    public function safeDown()
    {
        $this->execute('ALTER TABLE ss_mail_template MODIFY name VARCHAR(255)');
    }
}
