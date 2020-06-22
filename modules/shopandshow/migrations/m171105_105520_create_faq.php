<?php

use yii\db\Migration;

class m171105_105520_create_faq extends Migration
{
    const TABLE_NAME = '{{%cms_content_element_faq}}';

    public function safeUp()
    {
        $tableOptions = null;

        $tableExist = $this->db->getTableSchema(self::TABLE_NAME, true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'element_id' => $this->integer()->notNull(),

            'username' => $this->string(128),
            'email' => $this->string(128),

            'question' => $this->text(),
            'answer' => $this->text(),

            'like' => $this->integer(),
            'dislike' => $this->integer(),

            'status' => $this->integer(1)->unsigned()->notNull()->defaultValue(0)->comment('0-pending,1-published,2-spam,3-deleted'),
            'user_ip' => $this->string(15),
            'url' => $this->string(255),
        ], $tableOptions);

        $this->createIndex('comment_status_element_id', self::TABLE_NAME, ['status', 'element_id']);

        $this->addForeignKey(
            'cms_comment__created_by', self::TABLE_NAME,
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'cms_comment__updated_by', self::TABLE_NAME,
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        $this->dropIndex('comment_status_element_id', self::TABLE_NAME);
        $this->dropIndex('cms_comment__created_by', self::TABLE_NAME);
        $this->dropIndex('cms_comment__updated_by', self::TABLE_NAME);

        $this->dropTable(self::TABLE_NAME);
    }
}
