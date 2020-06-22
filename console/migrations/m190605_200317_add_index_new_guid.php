<?php

use yii\db\Migration;

class m190605_200317_add_index_new_guid extends Migration
{

    public function safeUp()
    {
        $this->createIndex('index_cms_content_element_new_guid','{{%cms_content_element}}', 'new_guid', true);

    }

    public function safeDown()
    {
        $this->dropIndex('index_cms_content_element_new_guid','{{%cms_content_element}}');


    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190605_200317_add_index_new_guid cannot be reverted.\n";

        return false;
    }
    */
}
