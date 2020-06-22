<?php

use yii\db\Migration;

/**
 * Handles the creation of table `promo_to_tree`.
 */
class m200124_101644_create_promo_to_tree_table extends Migration
{
    use \ignatenkovnikita\migrationsaddons\AddCreatedUpdated;
    use \ignatenkovnikita\migrationsaddons\AddAuthorUpdater;
    use \ignatenkovnikita\migrationsaddons\ForeignKeyTrait;

    const TABLE_PROMO = '{{%promo}}';
    const TABLE_TREE = '{{%cms_tree}}';
    const TABLE_NAME = '{{%promo_tree}}';


    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'promo_id' =>$this->integer()->notNull(),
            'tree_id' =>$this->integer()->notNull(),
        ]);

        $this->createIndex('index_promo_id_tree_id',self::TABLE_NAME,['promo_id','tree_id'],true);

        $this->addForeignKeys(self::TABLE_NAME, [
            ['promo_id', self::TABLE_PROMO, 'id'],
            ['tree_id', self::TABLE_TREE, 'id'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {

        $this->dropForeignKeys(self::TABLE_NAME, [
            ['promo_id', self::TABLE_PROMO, 'id'],
            ['tree_id', self::TABLE_TREE, 'id'],
        ]);

        $this->dropTable(self::TABLE_NAME);

        return true;
    }
}