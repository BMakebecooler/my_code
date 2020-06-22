<?php

use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentProperty;
use yii\db\Migration;

class m180403_115806_add_brand_property extends Migration
{
    const CODE = 'BRAND';
    const CONTENT_CODE = 'brand';

    public function safeUp()
    {
        $cmsContentProperty = CmsContentProperty::findOne(['code' => self::CODE, 'content_id' => PRODUCT_CONTENT_ID]);
        if (!$cmsContentProperty) {

            $cmsContentProperty = new CmsContentProperty();
            $cmsContentProperty->name = 'Бренд';
            $cmsContentProperty->code = self::CODE;
            $cmsContentProperty->content_id = PRODUCT_CONTENT_ID;
            $cmsContentProperty->active = \skeeks\cms\components\Cms::BOOL_Y;
            $cmsContentProperty->property_type = 'L';
            $cmsContentProperty->list_type = 'L';
            $cmsContentProperty->multiple = \skeeks\cms\components\Cms::BOOL_N;
            $cmsContentProperty->component = 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeElement';
            $cmsContentProperty->vendor_id = 170;

            if (!$cmsContentProperty->save()) {
                var_dump($cmsContentProperty->getErrors());
            }
        }

        $cmsContent = CmsContent::findOne(['code' => self::CONTENT_CODE]);
        if (!$cmsContent) {

            $cmsContent = new CmsContent([
                'name' => 'Бренды',
                'code' => self::CONTENT_CODE,
                'active' => \skeeks\cms\components\Cms::BOOL_Y,
                'priority' => 500,
                'name_meny' => 'Бренды',
                'name_one' => 'Бренд',
                'content_type' => 'info',
                'default_tree_id' => null,
                'is_allow_change_tree' => \skeeks\cms\components\Cms::BOOL_Y,
                'root_tree_id' => null,
                'viewFile' => null,
                'access_check_element' => \skeeks\cms\components\Cms::BOOL_N
            ]);

            if (!$cmsContent->save()) {
                var_dump($cmsContent->getErrors());
            }
        }
    }

    public function safeDown()
    {
        CmsContentProperty::deleteAll(['code' => self::CODE, 'content_id' => PRODUCT_CONTENT_ID]);
        CmsContent::deleteAll(['code' => self::CONTENT_CODE]);
    }
}
