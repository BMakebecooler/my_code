<?php

use modules\shopandshow\models\newEntities\common\CmsContentElementModel;
use yii\db\Migration;

/**
 * Class m180116_101307_add_content_type
 */
class m180116_101307_add_content_type extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->insert('cms_content_type', [
            'name' => 'KFFS справочник дополнительных свойств',
            'code' => CmsContentElementModel::CONTENT_TYPE_KFSS_INFO_PROPERTY
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        \skeeks\cms\models\CmsContentType::deleteAll(['code' => CmsContentElementModel::CONTENT_TYPE_KFSS_INFO_PROPERTY]);
    }
}
