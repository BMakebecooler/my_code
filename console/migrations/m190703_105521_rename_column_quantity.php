<?php

use yii\db\Migration;

class m190703_105521_rename_column_quantity extends Migration
{
    const TABLE_SHOP_PRODUCT = 'shop_product';
    const TABLE_SHOP_PRODUCT_DELETE = 'shop_product_delete';

    public function safeUp()
    {
        $this->renameColumn(self::TABLE_SHOP_PRODUCT, 'quantity', 'quantity_delete');

    }

    public function safeDown()
    {
        $this->renameColumn(self::TABLE_SHOP_PRODUCT, 'quantity_delete', 'quantity');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190703_105521_rename_column_quantity cannot be reverted.\n";

        return false;
    }
    */
}
