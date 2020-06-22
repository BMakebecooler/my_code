<?php

use yii\db\Migration;

class m180605_122659_add_column_to_ss_shares_schedule extends Migration
{
    private $tableName = '{{%ss_shares_schedule}}';

    public function safeUp()
    {
        $this->addColumn($this->tableName, 'tree_id', $this->integer()->after('block_type'));
    }

    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'tree_id');
    }
}
