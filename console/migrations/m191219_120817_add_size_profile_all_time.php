<?php

use yii\db\Migration;

class m191219_120817_add_size_profile_all_time extends Migration
{
    use \ignatenkovnikita\migrationsaddons\AddCreatedUpdated;
    use \ignatenkovnikita\migrationsaddons\AddAuthorUpdater;
    use \ignatenkovnikita\migrationsaddons\ForeignKeyTrait;

    const TABLE_NAME = '{{%size_profile}}';

    public function safeUp()
    {
        $this->addAllTime(self::TABLE_NAME);
    }

    public function safeDown()
    {
        echo "m191219_120817_add_size_profile_all_time cannot be reverted.\n";

        return false;
    }

}
