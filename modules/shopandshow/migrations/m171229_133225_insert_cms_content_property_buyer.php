<?php

use common\models\cmsContent\CmsContentProperty;
use yii\db\Migration;

/**
 * Class m171229_133225_insert_cms_content_property_buyer
 */
class m171229_133225_insert_cms_content_property_buyer extends Migration
{
    const CODE = 'BUYER_GUID';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $cmsContentProperty = CmsContentProperty::findOne(['code' => self::CODE]);
        if ($cmsContentProperty) return;

        $cmsContentProperty = new CmsContentProperty();
        $cmsContentProperty->name = 'Байер GUID';
        $cmsContentProperty->code = self::CODE;
        $cmsContentProperty->content_id = PRODUCT_CONTENT_ID;
        $cmsContentProperty->active = \skeeks\cms\components\Cms::BOOL_Y;
        $cmsContentProperty->property_type = 'S';
        $cmsContentProperty->list_type = 'L';
        $cmsContentProperty->multiple = \skeeks\cms\components\Cms::BOOL_N;
        $cmsContentProperty->component = 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeString';

        if(!$cmsContentProperty->save()) {
            var_dump($cmsContentProperty->getErrors());
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        CmsContentProperty::deleteAll(['code' => self::CODE]);
    }
}
