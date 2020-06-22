<?php

use yii\db\Migration;

class m171107_111121_add_columns_to_ss_share_schedule extends Migration
{
    private $tableName = '{{%ss_shares_schedule}}';

    public function safeUp()
    {
        $this->addColumn($this->tableName, 'name', $this->string());
        $this->addColumn($this->tableName, 'description', $this->string());
    }

    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'name');
        $this->dropColumn($this->tableName, 'description');
    }

}
