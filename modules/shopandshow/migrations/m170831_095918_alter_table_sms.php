<?php

use yii\db\Migration;

class m170831_095918_alter_table_sms extends Migration
{
    public function safeUp()
    {
        $this->addColumn('sms', 'fuser_id', $this->integer());
        $this->addColumn('sms', 'ip', $this->char(15));
    }

    public function safeDown()
    {
        $this->dropColumn('sms', 'fuser_id');
        $this->dropColumn('sms', 'ip');
    }

}
