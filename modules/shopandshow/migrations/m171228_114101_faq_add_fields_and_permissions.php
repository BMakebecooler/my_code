<?php

use common\models\cmsContent\ContentElementFaq;
use yii\db\Migration;

/**
 * Class m171228_114101_faq_add_fields_and_permissions
 */
class m171228_114101_faq_add_fields_and_permissions extends Migration
{
    private $tableName = 'cms_content_element_faq';
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'editor_lastview_at', $this->integer());
        $this->addColumn($this->tableName, 'sent_service_at', $this->integer());
        $this->addColumn($this->tableName, 'sent_buyer_at', $this->integer());
        $this->addColumn($this->tableName, 'published_at', $this->integer());

        $this->addColumn($this->tableName, 'buyer_answer', $this->text());
        $this->addColumn($this->tableName, 'service_answer', $this->text());
        $this->addColumn($this->tableName, 'copyright_answer', $this->text());

        $this->update($this->tableName, ['published_at' => new \yii\db\Expression('created_at + 1800')]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'editor_lastview_at');
        $this->dropColumn($this->tableName, 'sent_service_at');
        $this->dropColumn($this->tableName, 'sent_buyer_at');
        $this->dropColumn($this->tableName, 'published_at');

        $this->dropColumn($this->tableName, 'buyer_answer');
        $this->dropColumn($this->tableName, 'service_answer');
        $this->dropColumn($this->tableName, 'copyright_answer');
    }
}
