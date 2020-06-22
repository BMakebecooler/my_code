<?php

use yii\db\Migration;

class m190802_074935_add_sort_product_segment extends Migration
{
    const TABLE_SEGMENT_PRODUCT = '{{%segment_products}}';
    const TABLE_SEGMENT = '{{%segment}}';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_SEGMENT, 'first_products', $this->text());
        $this->addColumn(self::TABLE_SEGMENT_PRODUCT, 'sort', $this->integer(11)->defaultValue(0));
    }

    public function safeDown()
    {
        echo "m190802_074935_add_sort_product_segment cannot be reverted.\n";

        $this->dropColumn(self::TABLE_SEGMENT, 'first_products');
        $this->dropColumn(self::TABLE_SEGMENT_PRODUCT, 'sort');

        return true;
    }
}