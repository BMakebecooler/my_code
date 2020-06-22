<?php

use yii\db\Migration;

class m190704_123704_new_entities_segment_filter extends Migration
{
    use \ignatenkovnikita\migrationsaddons\AddCreatedUpdated;
    use \ignatenkovnikita\migrationsaddons\AddAuthorUpdater;
    use \ignatenkovnikita\migrationsaddons\ForeignKeyTrait;

    const TABLE_SEGMENT = '{{%segment}}';
    const TABLE_SEGMENT_FILTER_CATEGORIES = '{{%segment_filter_categories}}';
    const TABLE_SEGMENT_FILTERS = '{{%segment_filters}}';
    const TABLE_SEGMENT_TO_FILTERS = '{{%segment_segment_filters}}';

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(self::TABLE_SEGMENT, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'description' => $this->text(),
            'active' =>$this->boolean()->defaultValue(1),

            'generated' =>$this->boolean()->defaultValue(0)->comment('Выборка генерируется из условий или создается статически'),
            'generated_file' => $this->string(),

            'products' => $this->text(),
            'price_from' => $this->float(11.2),
            'price_to' => $this->float(11.2),

            'sale_from' => $this->float(11.2),
            'sale_to' => $this->float(11.2),

            'sort' => $this->string()->notNull(),

            'etalon_clothing_size' => $this->text()->comment('эталонная шкала размер одежды'),
            'etalon_shoe_size' => $this->text()->comment('Эталонная шкала размер обуви'),
            'etalon_sock_size' => $this->text()->comment('Эталонная шкала размер носков'),
            'etalon_jewelry_size' => $this->text()->comment('Эталонная шкала размер украшений'),
            'etalon_textile_size' => $this->text()->comment('Эталонная шкала размер текстиля'),
            'etalon_pillow_size' => $this->text()->comment('Эталонная шкала размер подушки'),
            'etalon_bed_linen_size' => $this->text()->comment('Эталонная шкала размер постельного белья'),
            'etalon_bra_size' => $this->text()->comment('Эталонная шкала размер бюстгальтера'),
            'etalon_cap_size' => $this->text()->comment('Эталонная шкала размер шапок'),

            'color' => $this->text(),
            'brand' => $this->string(),
            'season' => $this->string(),
            'tree_ids' => $this->text(),
            'material' => $this->string(),
            'insert' => $this->string(),
            'price_types' => $this->text(),



        ],$tableOptions);

        $this->createTable(self::TABLE_SEGMENT_FILTER_CATEGORIES, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'description' => $this->text(),
        ],$tableOptions);

        $this->createTable(self::TABLE_SEGMENT_FILTERS, [
            'id' => $this->primaryKey(),
            'id_category' =>  $this->integer(),
            'name' => $this->string()->notNull(),
            'description' => $this->text(),
            'field' => $this->string()->notNull(),
            'operand' => $this->string()->notNull(),
            'table' => $this->string()->notNull(),
        ],$tableOptions);

        $this->addForeignKey(
            'segment_category_fk', self::TABLE_SEGMENT_FILTERS,
            'id_category', self::TABLE_SEGMENT_FILTER_CATEGORIES, 'id', 'SET NULL', 'SET NULL'
        );

        $this->createTable(self::TABLE_SEGMENT_TO_FILTERS, [
            'id' => $this->primaryKey(),
            'segment_id' => $this->integer()->notNull(),
            'filter_id' => $this->integer()->notNull(),
            'value' => $this->string()->notNull(),
        ]);

        $this->addForeignKeys(self::TABLE_SEGMENT_TO_FILTERS, [
            ['segment_id', self::TABLE_SEGMENT, 'id'],
            ['filter_id', self::TABLE_SEGMENT_FILTERS, 'id'],
        ]);

        $this->addAllTime(self::TABLE_SEGMENT);
        $this->addAllUser(self::TABLE_SEGMENT);

        $this->addAllTime(self::TABLE_SEGMENT_FILTER_CATEGORIES);
        $this->addAllUser(self::TABLE_SEGMENT_FILTER_CATEGORIES);

        $this->addAllTime(self::TABLE_SEGMENT_FILTERS);
        $this->addAllUser(self::TABLE_SEGMENT_FILTERS);

    }

    public function safeDown()
    {
        echo "m190704_123704_new_entities_segment_filter cannot be reverted.\n";

        $this->dropForeignKey(
            'segment_category_fk', self::TABLE_SEGMENT_FILTERS,
            'id_category', self::TABLE_SEGMENT_FILTER_CATEGORIES, 'id', 'SET NULL', 'SET NULL'
        );

        $this->dropForeignKeys(self::TABLE_SEGMENT_TO_FILTERS, [
            ['segment_id', self::TABLE_SEGMENT, 'id'],
            ['filter_id', self::TABLE_SEGMENT_FILTERS, 'id'],
        ]);

        $this->dropTable(self::TABLE_SEGMENT);
        $this->dropTable(self::TABLE_SEGMENT_FILTER_CATEGORIES);
        $this->dropTable(self::TABLE_SEGMENT_FILTERS);
        $this->dropTable(self::TABLE_SEGMENT_TO_FILTERS);

        return true;
    }
}
