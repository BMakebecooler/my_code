<?php

use yii\db\Migration;

class m190403_083507_add_table_setting extends Migration
{
    const TABLE_SETTING = '{{%setting}}';

    use \ignatenkovnikita\migrationsaddons\AddCreatedUpdated;
    use \ignatenkovnikita\migrationsaddons\AddAuthorUpdater;

    public function safeUp()
    {

        $this->createTable(self::TABLE_SETTING, [
            'id' => $this->primaryKey(),
            'free_delivery_price' => $this->string(),
            'phone_code' => $this->string(),
            'phone_number' => $this->string(),
        ]);

        $this->addAllUser(self::TABLE_SETTING);
        $this->addAllTime(self::TABLE_SETTING);


        $this->insert(self::TABLE_SETTING, ['free_delivery_price' => 1, 'phone_code' => '8 (800)', 'phone_number' => '301-60-10']);

    }

    public function safeDown()
    {
        $this->dropTable(self::TABLE_SETTING);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190403_083507_add_table_setting cannot be reverted.\n";

        return false;
    }
    */
}
