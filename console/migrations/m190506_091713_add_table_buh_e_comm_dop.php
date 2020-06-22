<?php

use yii\db\Migration;

class m190506_091713_add_table_buh_e_comm_dop extends Migration
{

    const TABLE_BUH_E_COMM_DOP = 'product_abc_addition';
    const TABLE_CMS_CONTENT_ELEMENT = 'cms_content_element';

    use \ignatenkovnikita\migrationsaddons\ForeignKeyTrait;

    public function safeUp()
    {

        $this->createTable(self::TABLE_BUH_E_COMM_DOP, [
            'id' => $this->primaryKey(),
            'source_id' => $this->integer(),
            'product_id' => $this->integer(),
            'order' => $this->integer()
        ]);

        $this->addForeignKeys(self::TABLE_BUH_E_COMM_DOP, [
            ['source_id', self::TABLE_CMS_CONTENT_ELEMENT, 'id'],
            ['product_id', self::TABLE_CMS_CONTENT_ELEMENT, 'id']
        ]);

    }

    public function safeDown()
    {
        $this->dropForeignKeys(self::TABLE_BUH_E_COMM_DOP, [
            ['source_id', self::TABLE_CMS_CONTENT_ELEMENT, 'id'],
            ['product_id', self::TABLE_CMS_CONTENT_ELEMENT, 'id']
        ]);

        $this->dropTable(self::TABLE_BUH_E_COMM_DOP);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190506_091713_add_table_buh_e_comm_dop cannot be reverted.\n";

        return false;
    }
    */
}
