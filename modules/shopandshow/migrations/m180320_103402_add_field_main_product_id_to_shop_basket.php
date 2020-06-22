<?php

use yii\db\Migration;

class m180320_103402_add_field_main_product_id_to_shop_basket extends Migration
{
    public function safeUp()
    {
        $this->addColumn('shop_basket', 'main_product_id', $this->integer());

        $this->createIndex('main_product_id', 'shop_basket', 'main_product_id');

        $this->addForeignKey('fk_shop_basket_main_product',
            'shop_basket', 'main_product_id',
            'cms_content_element', 'id',
            'CASCADE', 'CASCADE'
        );

        $this->update('shop_basket',
            ['main_product_id' => new \yii\db\Expression('(SELECT IFNULL(parent_content_element_id, id) FROM cms_content_element cce WHERE cce.id = shop_basket.product_id)')]
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_shop_basket_main_product', 'shop_basket');

        $this->dropIndex('main_product_id', 'shop_basket');

        $this->dropColumn('shop_basket', 'main_product_id');
    }
}
