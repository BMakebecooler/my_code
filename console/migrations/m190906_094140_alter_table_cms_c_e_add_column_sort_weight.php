<?php

use common\models\Product;
use yii\db\Migration;

class m190906_094140_alter_table_cms_c_e_add_column_sort_weight extends Migration
{
    const COLUMN = "sort_weight";

    public function safeUp()
    {
        $this->addColumn(Product::tableName(), self::COLUMN, $this->integer()->defaultValue(0));
        $this->createIndex(sprintf("%s_%s_%s", Product::tableName(),self::COLUMN, 'index'), Product::tableName(), self::COLUMN);
    }

    public function safeDown()
    {
        $this->dropIndex(sprintf("%s_%s_%s", Product::tableName(),self::COLUMN, 'index'), Product::tableName());
        $this->dropColumn(Product::tableName(), self::COLUMN);
    }
}
