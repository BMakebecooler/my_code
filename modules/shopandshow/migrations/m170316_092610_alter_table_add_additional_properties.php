<?php

/**
 * php ./yii migrate/up --migrationPath=@modules/shopandshow/migrations
 */


use yii\db\Migration;

class m170316_092610_alter_table_add_additional_properties extends Migration
{
    public function safeUp()
    {

        $this->addColumn('{{%cms_content_element}}', 'bitrix_id', $this->integer());
        $this->addColumn('{{%cms_user}}', 'bitrix_id', $this->integer());
        $this->addColumn('{{%shop_order}}', 'bitrix_id', $this->integer());

        $this->createIndex('bitrix_id', '{{%cms_content_element}}', 'bitrix_id');
        $this->createIndex('bitrix_id', '{{%cms_user}}', 'bitrix_id');
        $this->createIndex('bitrix_id', '{{%shop_order}}', 'bitrix_id');


        $this->execute("UPDATE cms_content_element ce
INNER JOIN cms_content_element_property ccep ON ce.id = ccep.element_id AND ccep.property_id = 34
SET ce.bitrix_id = ccep.value");

        return true;
    }

    public function safeDown()
    {
        $this->dropIndex('bitrix_id','{{%cms_content_element}}');
        $this->dropIndex('bitrix_id','{{%cms_user}}');
        $this->dropIndex('bitrix_id','{{%shop_order}}');

        $this->dropColumn('{{%cms_content_element}}', 'bitrix_id');
        $this->dropColumn('{{%cms_user}}', 'bitrix_id');
        $this->dropColumn('{{%shop_order}}', 'bitrix_id');

        return true;
    }
}
