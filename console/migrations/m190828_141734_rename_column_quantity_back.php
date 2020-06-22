<?php

use yii\db\Migration;

class m190828_141734_rename_column_quantity_back extends Migration
{
    const TABLE_SHOP_PRODUCT = 'shop_product';
    const COLUMN_NAME_DELETED = 'quantity_delete';
    const COLUMN_NAME_NORMAL = 'quantity';

    public function safeUp()
    {
        $this->renameColumn(self::TABLE_SHOP_PRODUCT, self::COLUMN_NAME_DELETED, self::COLUMN_NAME_NORMAL);

    }

    public function safeDown()
    {
        $this->renameColumn(self::TABLE_SHOP_PRODUCT, self::COLUMN_NAME_NORMAL, self::COLUMN_NAME_DELETED);

    }
}
