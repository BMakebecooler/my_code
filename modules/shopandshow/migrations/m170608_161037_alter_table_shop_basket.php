<?php

use yii\db\Migration;

class m170608_161037_alter_table_shop_basket extends Migration
{
    public function safeUp()
    {
        $this->addColumn('shop_basket', 'has_removed', $this->smallInteger()
            ->defaultValue(\modules\shopandshow\models\shop\ShopBasket::HAS_REMOVED_FALSE));
    }

    public function safeDown()
    {
        $this->dropColumn('shop_basket', 'has_removed');
    }
}
