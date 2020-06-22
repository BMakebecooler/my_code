<?php

use yii\db\Migration;

class m170821_145918_alter_table_shop_discount_add_column_image_id extends Migration
{
    public function safeUp()
    {
        $this->addColumn('shop_discount', 'image_id', $this->integer());

        $this->addForeignKey(
            'shop_discount_cms_storage_file',
            'shop_discount', 'image_id',
            '{{%cms_storage_file}}', 'id',
            'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('shop_discount_cms_storage_file', 'shop_discount');
        $this->dropColumn('shop_discount', 'image_id');
    }
}
