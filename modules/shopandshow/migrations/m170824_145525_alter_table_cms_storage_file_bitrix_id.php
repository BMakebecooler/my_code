<?php

use yii\db\Migration;

class m170824_145525_alter_table_cms_storage_file_bitrix_id extends Migration
{
    public function safeUp()
    {
        $this->addColumn('cms_content_element_image', 'bitrix_id', $this->integer());
        $this->addColumn('cms_storage_file', 'bitrix_id', $this->integer());
    }

    public function safeDown()
    {
        $this->dropColumn('cms_content_element_image', 'bitrix_id');
        $this->dropColumn('cms_storage_file', 'bitrix_id');
    }
}
