<?php

use yii\db\Migration;

class m170608_103747_alter_table_shares extends Migration
{

    public function safeUp()
    {
        $this->addColumn('ss_shares', 'bitrix_product_id', $this->integer()->unsigned());
    }

    public function safeDown()
    {
        $this->dropColumn('ss_shares', 'bitrix_product_id');
    }
}
