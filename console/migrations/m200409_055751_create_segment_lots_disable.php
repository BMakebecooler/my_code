<?php

use yii\db\Migration;

class m200409_055751_create_segment_lots_disable extends Migration
{
    use \ignatenkovnikita\migrationsaddons\ForeignKeyTrait;

    const TABLE_NAME = '{{%segment_lots_disable}}';
    const TABLE_SEGMENT = '{{%segment}}';
    const TABLE_PRODUCT = '{{%cms_content_element}}';

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'segment_id' => $this->integer(),
            'lot_id' => $this->integer(),
        ], $tableOptions);

        $this->addForeignKeys(self::TABLE_NAME, [
            ['segment_id', self::TABLE_SEGMENT, 'id'],
            ['lot_id', self::TABLE_PRODUCT, 'id'],
        ]);

    }

    public function safeDown()
    {
        $this->dropForeignKeys(self::TABLE_NAME, [
            ['segment_id', self::TABLE_SEGMENT, 'id'],
            ['lot_id', self::TABLE_PRODUCT, 'id'],
        ]);

        $this->dropTable(self::TABLE_NAME);

        return true;
    }

}
