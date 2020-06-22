<?php

use yii\db\Migration;

class m170912_141450_create_table_ss_mail_template extends Migration
{
    private $tableName = "{{%ss_mail_template}}";

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'active' => $this->string(1)->notNull(),
            'name' => $this->string(255)->notNull(),
            'template' => $this->string(1024)->notNull(),
            'from' => $this->string(255)->notNull(),
            'tree_id' => $this->integer()
        ], $tableOptions);

        $this->createIndex('I_tree_id', $this->tableName, 'tree_id');

        $this->addForeignKey(
            'ss_mail_template_cms_tree',
            $this->tableName, 'tree_id',
            '{{%cms_tree}}', 'id'
        );
    }

    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
