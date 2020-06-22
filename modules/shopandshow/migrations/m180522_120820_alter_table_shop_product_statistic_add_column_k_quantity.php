<?php

use yii\db\Migration;

class m180522_120820_alter_table_shop_product_statistic_add_column_k_quantity extends Migration
{
    public function safeUp()
    {
        $this->addColumn('shop_product_statistic', 'k_quantity', $this->float());
    }

    public function safeDown()
    {
        $this->dropColumn('shop_product_statistic', 'k_quantity');
    }
}
