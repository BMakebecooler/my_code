<?php

use yii\db\Migration;

class m190628_120829_add_fields_product_param extends Migration
{

    const TABLE_PRODUCT_PARAM_PRODUCT = '{{%product_param_product}}';

    public function safeUp()
    {
        $this->truncateTable(self::TABLE_PRODUCT_PARAM_PRODUCT);
        $this->addColumn(self::TABLE_PRODUCT_PARAM_PRODUCT,'card_id',$this->integer(11));
        $this->addColumn(self::TABLE_PRODUCT_PARAM_PRODUCT,'lot_id',$this->integer(11));

        $this->createIndex('product_param_product_card_id',self::TABLE_PRODUCT_PARAM_PRODUCT,['card_id']);
        $this->createIndex('product_param_product_lot_id',self::TABLE_PRODUCT_PARAM_PRODUCT,['lot_id']);

    }

    public function safeDown()
    {
        echo "m190628_120829_add_fields_product_param cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190628_120829_add_fields_product_param cannot be reverted.\n";

        return false;
    }
    */
}
