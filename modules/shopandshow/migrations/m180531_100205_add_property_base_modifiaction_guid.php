<?php

use common\models\cmsContent\CmsContentProperty;
use yii\db\Migration;

class m180531_100205_add_property_base_modifiaction_guid extends Migration
{
    private $propertyCode = 'base_modification_guid';

    public function safeUp()
    {
        $cmsContentProperty = CmsContentProperty::findOne(['code' => $this->propertyCode, 'content_id' => PRODUCT_CONTENT_ID]);
        if (!$cmsContentProperty) {

            $cmsContentProperty = new CmsContentProperty();
            $cmsContentProperty->name = 'GUID базовой модификации';
            $cmsContentProperty->code = $this->propertyCode;
            $cmsContentProperty->content_id = PRODUCT_CONTENT_ID;
            $cmsContentProperty->active = \skeeks\cms\components\Cms::BOOL_Y;
            $cmsContentProperty->property_type = 'S';
            $cmsContentProperty->list_type = 'L';
            $cmsContentProperty->multiple = \skeeks\cms\components\Cms::BOOL_N;
            $cmsContentProperty->component = 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText';
            $cmsContentProperty->is_required = \skeeks\cms\components\Cms::BOOL_N;
            $cmsContentProperty->with_description = \skeeks\cms\components\Cms::BOOL_N;

            if (!$cmsContentProperty->save(false)) {
                var_dump($cmsContentProperty->getErrors());
                var_dump($cmsContentProperty->attributes);
            }
        }
    }

    public function safeDown()
    {
        CmsContentProperty::deleteAll(['code' => $this->propertyCode, 'content_id' => PRODUCT_CONTENT_ID]);
    }
}
