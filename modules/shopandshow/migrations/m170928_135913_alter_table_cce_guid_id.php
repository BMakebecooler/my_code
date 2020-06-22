<?php

use yii\db\Migration;

class m170928_135913_alter_table_cce_guid_id extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{cms_content_element}}', 'guid_id', $this->integer());
        $this->createIndex('I_guid_id', '{{cms_content_element}}', 'guid_id');
    }

    public function safeDown()
    {
        $this->dropIndex('guid_id','{{cms_content_element}}');
        $this->dropColumn('{{cms_content_element}}', 'guid_id');
    }

}
