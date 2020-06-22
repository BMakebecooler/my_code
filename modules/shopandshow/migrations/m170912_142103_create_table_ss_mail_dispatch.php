<?php

use yii\db\Migration;

class m170912_142103_create_table_ss_mail_dispatch extends Migration
{
    private $tableName = "{{%ss_mail_dispatch}}";

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'mail_template_id' => $this->integer()->notNull(),
            'status' => $this->string(32)->notNull(),
            'from' => $this->string(255)->notNull(),
            'to' => $this->string(1024),
            'subject' => $this->string(512)->notNull(),
            'body' => $this->text()

        ], $tableOptions);

        $this->createIndex('I_mail_template_id', $this->tableName, 'mail_template_id');

        $this->addForeignKey(
            'ss_mail_dispatch_ss_mail_template',
            $this->tableName, 'mail_template_id',
            '{{%ss_mail_template}}', 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
