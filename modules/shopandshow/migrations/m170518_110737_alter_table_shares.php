<?php

use yii\db\Migration;

class m170518_110737_alter_table_shares extends Migration
{
    public function safeUp()
    {
        $this->addColumn('ss_shares', 'bitrix_info_block_id', $this->integer());
    }

    public function safeDown()
    {
        $this->dropColumn('ss_shares', 'bitrix_info_block_id');
    }
}
