<?php

use yii\db\Migration;

class m180929_082738_alter_table_properies_admin_visible extends Migration
{
    public function safeUp()
    {
        try {
            $this->addColumn('cms_content_property', 'is_admin_show', $this->string(1)->defaultValue('N'));
        } catch (Exception $exception) {
            return true;
        }
    }

    public function safeDown()
    {
        try {
            $this->dropColumn('cms_content_property', 'is_admin_show');
        } catch (Exception $exception) {
            return true;
        }
    }
}
