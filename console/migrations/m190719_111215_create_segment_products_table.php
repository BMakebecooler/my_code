<?php

use yii\db\Migration;

/**
 * Handles the creation of table `segment_products`.
 */
class m190719_111215_create_segment_products_table extends Migration
{
    use \ignatenkovnikita\migrationsaddons\AddCreatedUpdated;
    use \ignatenkovnikita\migrationsaddons\AddAuthorUpdater;
    use \ignatenkovnikita\migrationsaddons\ForeignKeyTrait;

    const TABLE_PRODUCT = '{{%cms_content_element}}';
    const TABLE_SEGMENT_PRODUCT = '{{%segment_products}}';
    const TABLE_SEGMENT = '{{%segment}}';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(self::TABLE_SEGMENT_PRODUCT, [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'segment_id' => $this->integer()->notNull(),
        ],$tableOptions);

        $this->createIndex('index_segment_products_product_id_segment_id',self::TABLE_SEGMENT_PRODUCT,['product_id','segment_id'],true);

        $this->addForeignKeys(self::TABLE_SEGMENT_PRODUCT, [
            ['product_id', self::TABLE_PRODUCT, 'id'],
            ['segment_id', self::TABLE_SEGMENT, 'id'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKeys(self::TABLE_SEGMENT_PRODUCT, [
            ['product_id', self::TABLE_PRODUCT, 'id'],
            ['segment_id', self::TABLE_SEGMENT, 'id'],
        ]);

        $this->dropTable(self::TABLE_SEGMENT_PRODUCT);
    }
}
