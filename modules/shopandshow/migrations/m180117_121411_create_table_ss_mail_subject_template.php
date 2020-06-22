<?php

use yii\db\Migration;

/**
 * Class m180117_121411_create_table_ss_mail_subject_template
 */
class m180117_121411_create_table_ss_mail_subject_template extends Migration
{
    private $tableName = "{{%ss_mail_subject}}";

    /**
     * @inheritdoc
     */
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

            'name' => $this->string(255)->notNull()->comment('Название'),
            'active' => $this->string(1)->notNull()->comment('Активность'),

            'begin_datetime' => $this->integer()->comment('Время начала действия заголовка'),
            'end_datetime' => $this->integer()->comment('Время окончания действия заголовка'),

            'subject' => $this->string(255)->notNull()->comment('Тема рассылки'),

            'template_id' => $this->integer()->comment('Ссылка на шаблон рассылки')
        ], $tableOptions);

        $this->createIndex('I_template_id', $this->tableName, 'template_id');

        // FK на шаблон рассылки
        $this->addForeignKey(
            'ss_mail_subject_mail_template',
            $this->tableName, 'template_id',
            '{{%ss_mail_template}}', 'id'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
