<?php

use yii\db\Migration;

/**
 * Class m180205_151815_add_field_is_base_to_cms_content_element
 */
class m180205_151815_add_field_is_base_to_cms_content_element extends Migration
{
    private $tableName = '{{%cms_content_element%}}';
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'is_base',
            $this->char(1)->defaultValue(\skeeks\cms\components\Cms::BOOL_N)->after('bitrix_id')->comment('Фейковая модификация')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'is_base');
    }
}
