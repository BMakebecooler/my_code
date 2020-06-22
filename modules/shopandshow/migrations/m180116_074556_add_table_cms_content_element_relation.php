<?php

use yii\db\Migration;

/**
 * Class m180116_074556_add_table_cms_content_element_relation
 */
class m180116_074556_add_table_cms_content_element_relation extends Migration
{
    private $tableName = '{{%cms_content_element_relation}}';
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
            'content_element_id' => $this->integer(),
            'related_content_element_id' => $this->integer()
        ], $tableOptions);

        $this->addPrimaryKey('cms_content_element_relation_pk', $this->tableName, ['content_element_id', 'related_content_element_id']);
        $this->addForeignKey('cms_content_element_relation_fk', $this->tableName, 'content_element_id', 'cms_content_element', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('cms_content_element_relation_related_fk', $this->tableName, 'related_content_element_id', 'cms_content_element', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }

}
