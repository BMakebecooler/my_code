<?php

use yii\db\Migration;

class m190709_074520_add_promo_table extends Migration
{

    use \ignatenkovnikita\migrationsaddons\AddCreatedUpdated;
    use \ignatenkovnikita\migrationsaddons\AddAuthorUpdater;
    use \ignatenkovnikita\migrationsaddons\ForeignKeyTrait;

    const TABLE_PROMO = '{{%promo}}';
    const TABLE_SEGMENT = '{{%segment}}';

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(self::TABLE_PROMO, [
            'id' => $this->primaryKey(),
            'active' =>$this->boolean()->defaultValue(1),
            'name' => $this->string()->notNull(),
            'link' => $this->string()->notNull()->unique(),
            'description' => $this->text(),
            'meta_description' => $this->text(),
            'meta_title' => $this->string(),
            'meta_keywords' => $this->string(),
            'segment_id' => $this->integer(),
            'image' => $this->string(),
        ],$tableOptions);

        $this->addForeignKey(
            'segment_fk', self::TABLE_PROMO ,
            'segment_id', self::TABLE_SEGMENT, 'id', 'SET NULL', 'SET NULL'
        );

        $this->addAllTime(self::TABLE_PROMO);
        $this->addAllUser(self::TABLE_PROMO);

    }

    public function safeDown()
    {
        echo "m190709_074520_add_promo_table cannot be reverted.\n";

        $this->dropForeignKey(
            'segment_fk', self::TABLE_PROMO ,
            'segment_id', self::TABLE_SEGMENT, 'id', 'SET NULL', 'SET NULL'
        );

        $this->dropTable(self::TABLE_PROMO );

        return true;
    }
}
