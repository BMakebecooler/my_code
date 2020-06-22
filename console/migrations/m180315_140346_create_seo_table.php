<?php

use yii\db\Migration;

/**
 * Handles the creation of table `m180315_140346_create_seo_table`.
 */
class m180315_140346_create_seo_table extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%seo}}', [
            'id' => $this->primaryKey(),
            'owner' => $this->string()->notNull(),
            'owner_id' => $this->integer(),

            'h1' => $this->string()->notNull(),
            'title' => $this->string(512)->notNull(),
            'slug' => $this->string(1024)->notNull(),

            'meta_keywords' => $this->text(),
            'meta_description' => $this->text(),
            'meta_index' => $this->string(1024)->notNull(),
            'redirect_301' => $this->string(1024)
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%seo}}');
    }
}
