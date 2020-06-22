<?php

use yii\db\Migration;

class m170516_133841_alter_table_prices extends Migration
{
    public function safeUp()
    {

        $this->addColumn('ss_shop_product_prices', 'discount_percent', $this->integer());

        $this->execute("UPDATE ss_shop_product_prices ce
SET ce.discount_percent = ROUND(((price - min_price) / price) * 100 )");
    }

    public function safeDown()
    {
        $this->dropColumn('ss_shop_product_prices', 'discount_percent');
    }
}
