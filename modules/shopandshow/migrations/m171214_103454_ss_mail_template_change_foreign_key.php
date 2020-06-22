<?php

use yii\db\Migration;

/**
 * Class m171214_103454_ss_mail_template_change_foreign_key
 */
class m171214_103454_ss_mail_template_change_foreign_key extends Migration
{
    private $tableName = "{{%ss_mail_template}}";

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropForeignKey('ss_mail_template_cms_tree', $this->tableName);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addForeignKey(
            'ss_mail_template_cms_tree',
            $this->tableName, 'tree_id',
            '{{%cms_tree}}', 'id'
        );

        return false;
    }
}
