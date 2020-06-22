<?php

use yii\db\Migration;

class m171024_102719_create_table_user_votes extends Migration
{
    private $tableName = "{{%ss_user_vote}}";

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'cms_user_id' => $this->integer()->notNull(),
            'cms_content_element_id' => $this->integer()->notNull(),
            'value' => $this->string(64),
            'vote_id' => $this->integer()->notNull()
        ], $tableOptions);

        $this->addForeignKey(
            'ss_user_vote_cms_user',
            $this->tableName, 'cms_user_id',
            'cms_user', 'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'ss_user_vote_cms_content_element_id',
            $this->tableName, 'cms_content_element_id',
            'cms_content_element', 'id',
            'CASCADE'
        );

        // поле для указания типа голосований (например "лукбук клиентов")
        $this->createIndex('vote_id', $this->tableName, 'vote_id');
    }

    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
