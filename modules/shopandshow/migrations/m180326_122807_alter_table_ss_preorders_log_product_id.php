<?php

use yii\db\Migration;

class m180326_122807_alter_table_ss_preorders_log_product_id extends Migration
{

    public function safeUp()
    {
        $this->addColumn('ss_preorders_logs', 'products_ids', $this->string(256));
    }

    public function safeDown()
    {
        $this->dropColumn('ss_preorders_logs', 'products_ids');
    }
}
