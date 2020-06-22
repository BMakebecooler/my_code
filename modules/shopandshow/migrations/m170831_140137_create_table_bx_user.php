<?php

use yii\db\Migration;

class m170831_140137_create_table_bx_user extends Migration
{
    private $tableName = "contact_data_bitrix_users";

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        // типы акций и условия
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'phone' => $this->string(),
            'email' => $this->string()->notNull(),
        ], $tableOptions);

        $this->createIndex('I_phone', $this->tableName, 'phone');
        $this->createIndex('I_email', $this->tableName, 'email');

        $this->addForeignKey(
            'FK_contact_data_bitrix_user',
            $this->tableName, 'id',
            '{{%cms_user}}', 'bitrix_id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
