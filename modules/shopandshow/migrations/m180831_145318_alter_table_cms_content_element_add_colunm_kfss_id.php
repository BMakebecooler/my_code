<?php

use yii\db\Migration;

class m180831_145318_alter_table_cms_content_element_add_colunm_kfss_id extends Migration
{
    private $tableName = "{{%cms_content_element}}";
    private $columnAdd = 'kfss_id';

    public function safeUp()
    {
        $this->addColumn($this->tableName, $this->columnAdd, $this->integer()->unsigned());
    }

    public function safeDown()
    {
        $this->dropColumn($this->tableName, $this->columnAdd);
    }
}
