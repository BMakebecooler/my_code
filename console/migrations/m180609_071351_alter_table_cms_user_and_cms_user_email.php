<?php

use yii\db\Migration;

/**
 * Class m180609_071351_alter_table_cms_user_and_cms_user_email
 */
class m180609_071351_alter_table_cms_user_and_cms_user_email extends Migration
{
    private $tableNameCmsUser       = '{{%cms_user}}';
    private $tableNameCmsUserEmail  = '{{%cms_user_email}}';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        try {
            $this->addColumn($this->tableNameCmsUser, 'source', $this->string());
            $this->addColumn($this->tableNameCmsUser, 'source_detail', $this->string());

            $this->addColumn($this->tableNameCmsUserEmail, 'is_valid_site', $this->string(1));

        } catch (Exception $exception) {
            return true;
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        try {
            $this->dropColumn($this->tableNameCmsUser, 'source');
            $this->dropColumn($this->tableNameCmsUser, 'source_detail');

            $this->dropColumn($this->tableNameCmsUserEmail, 'is_valid_site');

        } catch (Exception $exception) {
            return true;
        }
    }
}
