<?php

use yii\db\Migration;

class m191213_135122_create_table_segment_cards_disable extends Migration
{
    use \ignatenkovnikita\migrationsaddons\AddCreatedUpdated;
    use \ignatenkovnikita\migrationsaddons\AddAuthorUpdater;
    use \ignatenkovnikita\migrationsaddons\ForeignKeyTrait;


    const TABLE_NAME = '{{%segment_cards_disable}}';
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
            'segment_id' =>  $this->integer(),
            'lot_id' =>  $this->integer(),
            'card_id' =>  $this->integer(),
        ],$tableOptions);

        $this->addForeignKey(
            'segment_fk', self::TABLE_NAME ,
            'segment_id', self::TABLE_SEGMENT, 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'card_fk', self::TABLE_NAME ,
            'card_id', self::TABLE_PRODUCT, 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'lot_fk', self::TABLE_NAME ,
            'lot_id', self::TABLE_PRODUCT, 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {

        $this->dropForeignKey(
            'segment_fk', self::TABLE_NAME ,
            'segment_id', self::TABLE_SEGMENT, 'id', 'SET NULL', 'SET NULL'
        );

        $this->dropForeignKey(
            'card_fk', self::TABLE_NAME ,
            'card_id', self::TABLE_PRODUCT, 'id', 'SET NULL', 'SET NULL'
        );

        $this->dropForeignKey(
            'lot_fk', self::TABLE_NAME ,
            'lot_id', self::TABLE_PRODUCT, 'id', 'SET NULL', 'SET NULL'
        );

        $this->dropTable(self::TABLE_NAME);
    }

}