<?php

use yii\db\Migration;

class m200124_110957_add_promo_tree_ids_column extends Migration
{

    const TABLE_NAME = '{{%promo}}';
    const COLUMN_NAME = '{{%promo}}';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::COLUMN_NAME, $this->string());
    }

    public function safeDown()
    {
        echo "m200124_110957_add_promo_tree_ids_column cannot be reverted.\n";

        $this->dropColumn(self::TABLE_NAME, self::COLUMN_NAME);

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200124_110957_add_promo_tree_ids_column cannot be reverted.\n";

        return false;
    }
    */
}