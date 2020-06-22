<?php

use yii\db\Migration;

class m171102_100903_alter_table_ss_share_add_column_description extends Migration
{
    private $tableName = 'ss_shares';

    public function safeUp()
    {
        $this->addColumn($this->tableName, 'description', $this->string());
    }

    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'description');
    }
}
