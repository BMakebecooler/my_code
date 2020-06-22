<?php

use yii\db\Migration;

class m170203_142219_add_field_cms_content_element extends Migration
{
    public function safeUp()
    {
        $this->addColumn("{{%cms_content_property}}", "vendor_id", $this->integer());
    }

    public function safeDown()
    {
        echo "m170203_142219_add_field_cms_content_element cannot be reverted.\n";
        return false;
    }
}
