<?php

use yii\db\Migration;

/**
 * Handles the creation of table `segment_product_card`.
 */
class m200317_090149_create_segment_product_card_table extends Migration
{
    use \ignatenkovnikita\migrationsaddons\ForeignKeyTrait;

    const TABLE_NAME = '{{%segment_product_card%}}';
    const TABLE_PRODUCT = '{{%cms_content_element}}';
    const TABLE_SEGMENT = '{{%segment}}';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'segment_id' => $this->integer(),
            'lot_id' => $this->integer(),
            'card_id' => $this->integer(),
            'sort' => $this->integer(),
            'qty' => $this->integer(),
            'first' => $this->integer(),
        ]);

        $this->createIndex('index_segment_products_lot_id_segment_id', self::TABLE_NAME, ['lot_id', 'card_id', 'segment_id'], true);

        $this->createIndex('sort_i', self::TABLE_NAME, 'sort');
        $this->createIndex('qty_i', self::TABLE_NAME, 'qty');
        $this->createIndex('first_i', self::TABLE_NAME, 'first');


        $this->addForeignKeys(self::TABLE_NAME, [
            ['lot_id', self::TABLE_PRODUCT, 'id'],
            ['segment_id', self::TABLE_SEGMENT, 'id'],
            ['card_id', self::TABLE_PRODUCT, 'id'],
        ]);

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKeys(self::TABLE_NAME, [
            ['lot_id', self::TABLE_PRODUCT, 'id'],
            ['segment_id', self::TABLE_SEGMENT, 'id'],
            ['card_id', self::TABLE_PRODUCT, 'id'],
        ]);

        $this->dropTable(self::TABLE_NAME);
    }
}
