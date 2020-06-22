<?php

use yii\db\Migration;

class m170906_093748_table_guids extends Migration
{
    private $tableName = 'ss_guids';

    public function safeUp()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey()->unsigned(),
            'guid' => $this->string(64),
            'entity_type' => $this->smallInteger()->unsigned()->notNull(),
        ], $tableOptions);

        $this->createIndex('ss_guid', $this->tableName, 'guid', true);

        $this->dropIndex('I_guid', 'cms_user');
        $this->dropColumn('cms_user', 'guid');

        $this->dropIndex('I_guid', 'shop_order');
        $this->dropColumn('shop_order', 'guid');

        $this->addColumn('cms_user', 'guid_id', $this->integer()->unsigned());
        $this->createIndex('cms_user_guid', 'cms_user', 'guid_id');

        $this->addColumn('shop_order', 'guid_id', $this->integer()->unsigned());
        $this->createIndex('cms_shop_order', 'shop_order', 'guid_id');

        $this->addColumn('contact_data_bitrix_users', 'guid', $this->string(64));
        $this->createIndex('contact_data_bitrix_users_guid', 'contact_data_bitrix_users', 'guid');
    }

    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
