<?php

use yii\db\Migration;

class m200403_013656_cms_ce_add_index_kfss_id extends Migration
{
    private $tableName = '{{%cms_content_element}}';
    private $indexKfssId = 'i_kfss_id';

    public function safeUp()
    {
        $this->createIndex($this->indexKfssId, $this->tableName, 'kfss_id');
    }

    public function safeDown()
    {
        $this->dropIndex($this->indexKfssId, $this->tableName);
    }
}
