<?php

use yii\db\Migration;

class m190731_085605_add_table_custom_menu extends Migration
{

    const TABLE_CUSTOM_MENU = '{{%custom_menu}}';

    use \ignatenkovnikita\migrationsaddons\AddAuthorUpdater;
    use \ignatenkovnikita\migrationsaddons\AddCreatedUpdated;

    public function safeUp()
    {
        $this->createTable(self::TABLE_CUSTOM_MENU,[
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'url' => $this->string()->notNull(),
            'is_active' => $this->boolean()->defaultValue(false),
            'type_id' => $this->integer()->notNull()
        ]);
        $this->addAllUser(self::TABLE_CUSTOM_MENU);
        $this->addAllTime(self::TABLE_CUSTOM_MENU);

    }

    public function safeDown()
    {
        $this->dropTable(self::TABLE_CUSTOM_MENU);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190731_085605_add_table_custom_menu cannot be reverted.\n";

        return false;
    }
    */
}
