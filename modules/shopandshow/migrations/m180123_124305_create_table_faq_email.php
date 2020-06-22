<?php

use yii\db\Migration;

/**
 * Class m180123_124305_create_table_faq_email
 */
class m180123_124305_create_table_faq_email extends Migration
{
    private $tableName = 'faq_email';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'group' => $this->string(255)->notNull()->comment('Отдел'),
            'type' => $this->string(255)->notNull()->comment('Тип'),
            'tree_id' => $this->integer(),
            'fio' => $this->string(),
            'email' => $this->string()
        ], $tableOptions);

        $this->createIndex('I_tree_id', $this->tableName, 'tree_id');

        // FK на шаблон рассылки
        $this->addForeignKey(
            'faq_email__tree_fk',
            $this->tableName, 'tree_id',
            '{{%cms_tree}}', 'id'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
