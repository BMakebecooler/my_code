<?php

use yii\db\Migration;

class m200401_074236_create_table_segment_log extends Migration
{
    use \ignatenkovnikita\migrationsaddons\AddCreatedUpdated;
    use \ignatenkovnikita\migrationsaddons\AddAuthorUpdater;

    const TABLE_NAME = '{{%log}}';

    public function safeUp()
    {

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'type' => $this->string(),
            'model_class' => $this->string(),
            'model_id' => $this->integer(),
            'text' => $this->string(),
        ], $tableOptions);
        $this->addAllTime(static::TABLE_NAME);
        $this->addAllUser(static::TABLE_NAME);

        $this->createIndex('model_class_i', self::TABLE_NAME, 'model_class');
        $this->createIndex('model_id_i', self::TABLE_NAME, 'model_id');

    }

    public function safeDown()
    {
        $this->dropTable(self::TABLE_NAME);
        return true;
    }
}
