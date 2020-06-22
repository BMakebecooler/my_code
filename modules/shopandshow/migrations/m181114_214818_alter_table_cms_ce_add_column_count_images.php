<?php

use yii\db\Migration;

class m181114_214818_alter_table_cms_ce_add_column_count_images extends Migration
{
    private $tableName = '{{%cms_content_element}}';
    private $column = 'count_images';

    public function safeUp()
    {
        $this->addColumn($this->tableName, $this->column, $this->integer()->unsigned()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn($this->tableName, $this->column);
    }
}
