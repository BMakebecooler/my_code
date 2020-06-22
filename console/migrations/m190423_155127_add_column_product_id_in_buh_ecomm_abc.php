<?php

use yii\db\Migration;

class m190423_155127_add_column_product_id_in_buh_ecomm_abc extends Migration
{
    const TABLE_NAME = 'buh_e_comm_abc';
    const TABLE_CMS_CONTENT_ELEMENT = 'cms_content_element';

    use \ignatenkovnikita\migrationsaddons\ForeignKeyTrait;

    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, 'product_id', $this->integer());

        $this->addForeignKeys(self::TABLE_NAME, [
            ['product_id', self::TABLE_CMS_CONTENT_ELEMENT, 'id']
        ]);
    }

    public function safeDown()
    {

        $this->dropForeignKeys(self::TABLE_NAME, [
            ['product_id', self::TABLE_CMS_CONTENT_ELEMENT, 'id']
        ]);

        $this->dropColumn(self::TABLE_NAME, 'product_id');


    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190423_155127_add_column_product_id_in_buh_ecomm_abc cannot be reverted.\n";

        return false;
    }
    */
}
