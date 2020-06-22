<?php

use yii\db\Migration;

class m170413_181630_alter_table_cms_tree extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%cms_tree}}', 'bitrix_id', $this->integer());
        $this->createIndex('bitrix_id', '{{%cms_tree}}', 'bitrix_id');

        $this->execute("UPDATE cms_tree ct
INNER JOIN cms_tree_property ctp ON ct.id = ctp.element_id AND ctp.property_id = 6
SET ct.bitrix_id = ctp.value");

    }

    public function safeDown()
    {
        $this->dropIndex('bitrix_id','{{%cms_tree}}');

        $this->dropColumn('{{%cms_tree}}', 'bitrix_id');
    }
}
