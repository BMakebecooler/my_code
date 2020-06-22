<?php

use yii\db\Migration;

class m180919_061441_add_table_segmentns_files extends Migration
{
    private $table_name = '{{%ss_segments_files}}';

    private $table_segments_name = '{{%ss_products_segments}}';


    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema($this->table_name, true);

        if ($tableExist) {
            return true;
        }

        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->table_name, [
            'id' => $this->primaryKey(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'file_id' => $this->integer(),

            'begin_datetime' => $this->integer()->notNull(),
            'end_datetime' => $this->integer()->notNull(),
            'name' => $this->string(),

        ], $tableOptions);

        $this->addColumn($this->table_segments_name, 'begin_datetime', $this->integer());
        $this->addColumn($this->table_segments_name, 'end_datetime', $this->integer());
        $this->addColumn($this->table_segments_name, 'file_id', $this->integer());

        $this->addForeignKey('fk_file_id', $this->table_segments_name, 'file_id', $this->table_name, 'id', 'CASCADE', 'CASCADE');

        $this->createIndex('i_begin_end_datetime', $this->table_segments_name, ['begin_datetime', 'end_datetime']);

        $this->dropIndex('i_product_id', $this->table_segments_name);
        $this->createIndex('i_product_id_file_id', $this->table_segments_name, ['product_id', 'file_id'], true);
    }

    public function safeDown()
    {

        $this->dropForeignKey('fk_file_id', $this->table_segments_name);

        $this->dropTable($this->table_name);

        $this->dropIndex('i_begin_end_datetime', $this->table_segments_name);

        $this->createIndex('i_product_id', $this->table_segments_name, 'product_id', true);
        $this->dropIndex('i_product_id_file_id', $this->table_segments_name);

        $this->dropColumn($this->table_segments_name, 'begin_datetime');
        $this->dropColumn($this->table_segments_name, 'end_datetime');
        $this->dropColumn($this->table_segments_name, 'file_id');
    }
}
