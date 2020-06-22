<?php

use yii\db\Migration;

class m191127_105015_create_size_profile_tables extends Migration
{
    use \ignatenkovnikita\migrationsaddons\AddCreatedUpdated;
    use \ignatenkovnikita\migrationsaddons\AddAuthorUpdater;
    use \ignatenkovnikita\migrationsaddons\ForeignKeyTrait;


    const TABLE_SIZE_PROFILE = '{{%size_profile}}';

    const TABLE_SIZE_PROFILE_PARAMS = '{{%size_profile_params}}';

    const TABLE_PARAMS = '{{%product_param}}';

//    const TABLE_USERS = '';

    public function safeUp()
    {

        $this->createTable(self::TABLE_SIZE_PROFILE,[
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'user_id' => $this->integer(),
            'session_id' => $this->string()->notNull(),
            'type' => $this->string()->notNull(),
            'description' => $this->string()
        ]);

        $this->createTable(self::TABLE_SIZE_PROFILE_PARAMS,[
            'id' => $this->primaryKey(),
            'size_profile_id' => $this->integer(),
            'type' => $this->string()->notNull(),
            'param_id' => $this->integer(),
        ]);

//        $this->createIndex('index_size_profile_params_size_profile_id_param_id',self::TABLE_SIZE_PROFILE_PARAMS,['size_profile_id','param_id','type'],true);

        $this->addForeignKeys(self::TABLE_SIZE_PROFILE_PARAMS, [
            ['size_profile_id', self::TABLE_SIZE_PROFILE, 'id'],
            ['param_id', self::TABLE_PARAMS, 'id'],
        ]);


    }

    public function safeDown()
    {
//        return true;
        echo "m191127_105015_create_TABLE_SIZE_PROFILEs cannot be reverted.\n";

        $this->dropForeignKeys(self::TABLE_SIZE_PROFILE, [
            ['size_profile_id', self::TABLE_SIZE_PROFILE, 'id'],
            ['param_id', self::TABLE_PARAMS, 'id'],
        ]);

        $this->dropTable(self::TABLE_SIZE_PROFILE);
        $this->dropTable(self::TABLE_SIZE_PROFILE_PARAMS);

        return true;
    }

}
