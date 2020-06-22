<?php

use common\models\Product;
use yii\db\Migration;

class m190905_090324_alter_table_cms_c_e_add_columns_badges extends Migration
{
    const COLUMN1 = "badge_1";
    const COLUMN2 = "badge_2";

    public function safeUp()
    {
        $this->addColumn(Product::tableName(), self::COLUMN1, $this->integer()->defaultValue(0));
        $this->addColumn(Product::tableName(), self::COLUMN2, $this->integer()->defaultValue(0));

        $this->createIndex(sprintf("%s_%s_%s", Product::tableName(),self::COLUMN1, 'index'), Product::tableName(), self::COLUMN1);
        $this->createIndex(sprintf("%s_%s_%s", Product::tableName(),self::COLUMN2, 'index'), Product::tableName(), self::COLUMN2);
    }

    public function safeDown()
    {
        $this->dropIndex(sprintf("%s_%s_%s", Product::tableName(),self::COLUMN2, 'index'), Product::tableName());
        $this->dropIndex(sprintf("%s_%s_%s", Product::tableName(),self::COLUMN1, 'index'), Product::tableName());

        $this->dropColumn(Product::tableName(), self::COLUMN2);
        $this->dropColumn(Product::tableName(), self::COLUMN1);
    }
}
