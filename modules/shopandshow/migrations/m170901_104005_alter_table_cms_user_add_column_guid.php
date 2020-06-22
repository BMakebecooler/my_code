<?php

use yii\db\Migration;

class m170901_104005_alter_table_cms_user_add_column_guid extends Migration
{
    public function safeUp()
    {
        $this->addColumn('cms_user', 'guid', $this->string(32));
        $this->createIndex('I_guid', 'cms_user', 'guid', true);
    }

    public function safeDown()
    {
        $this->dropIndex('I_guid', 'cms_user');
        $this->dropColumn('cms_user', 'guid');
    }
}
