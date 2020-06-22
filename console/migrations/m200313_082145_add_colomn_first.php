<?php

use yii\db\Migration;

class m200313_082145_add_colomn_first extends Migration
{
//    use \ignatenkovnikita\migrationsaddons\AddAuthorUpdater;
//    use \ignatenkovnikita\migrationsaddons\AddCreatedUpdated;

    const TABLE_NAME = '{{%segment_products}}';
    const COLUMN_NAME = 'first';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::COLUMN_NAME, $this->integer()->defaultValue(0));
        $this->createIndex(self::COLUMN_NAME.'_i', self::TABLE_NAME, self::COLUMN_NAME);
    }

    public function safeDown()
    {
        echo "m200313_082145_add_colomn_first cannot be reverted.\n";



        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200313_082145_add_colomn_first cannot be reverted.\n";

        return false;
    }
    */
}
