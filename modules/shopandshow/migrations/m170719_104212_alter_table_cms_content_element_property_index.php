<?php

use yii\db\Migration;

class m170719_104212_alter_table_cms_content_element_property_index extends Migration
{
    private $tableName = '{{%cms_content_element_property}}';

    public function safeUp()
    {
        $this->createIndex('I_element_id_property_id', $this->tableName, ['element_id', 'property_id']);
        $this->dropIndex('element_id', $this->tableName);
    }

    public function safeDown()

    {
        $this->createIndex('element_id', $this->tableName, ['element_id']);
        $this->dropIndex('I_element_id_property_id', $this->tableName);
    }
}
