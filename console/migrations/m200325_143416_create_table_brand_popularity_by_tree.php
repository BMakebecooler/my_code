<?php

use yii\db\Migration;

class m200325_143416_create_table_brand_popularity_by_tree extends Migration
{
    private $tableName = '{{%brand_popularity_by_tree}}';
    private $indexBrand = 'i_brand_id';
    private $indexTree = 'i_tree_id';

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable(
            $this->tableName,
            [
                'id' => $this->primaryKey(),
                'brand_id' => $this->integer()->notNull(),
                'tree_id' => $this->integer()->notNull(),
                'popularity' => $this->integer(),
            ],
            $tableOptions
        );

        $this->createIndex($this->indexBrand, $this->tableName, 'brand_id');
        $this->createIndex($this->indexTree, $this->tableName, 'tree_id');
    }

    public function safeDown()
    {
        $this->dropIndex($this->indexTree, $this->tableName);
        $this->dropIndex($this->indexBrand, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
