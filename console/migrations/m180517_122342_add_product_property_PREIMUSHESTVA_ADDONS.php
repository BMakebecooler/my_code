<?php

use common\models\cmsContent\CmsContentProperty;
use skeeks\cms\models\CmsContentElementProperty;
use yii\db\Migration;

/**
 * Class m180517_122342_add_product_property_PREIMUSHESTVA_ADDONS
 */
class m180517_122342_add_product_property_PREIMUSHESTVA_ADDONS extends Migration
{
    const CODE = 'PREIMUSHESTVA_ADDONS';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $cmsContentProperty = CmsContentProperty::findOne(['code' => self::CODE, 'content_id' => PRODUCT_CONTENT_ID]);
        if (!$cmsContentProperty) {

            $cmsContentProperty = new CmsContentProperty();
            $cmsContentProperty->name = 'Торг. преимущества (дополнения)';
            $cmsContentProperty->code = self::CODE;
            $cmsContentProperty->content_id = PRODUCT_CONTENT_ID;
            $cmsContentProperty->active = \skeeks\cms\components\Cms::BOOL_Y;
            $cmsContentProperty->property_type = 'L';
            $cmsContentProperty->list_type = 'L';
            $cmsContentProperty->multiple = \skeeks\cms\components\Cms::BOOL_N;
            $cmsContentProperty->component = 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeElement';

            if (!$cmsContentProperty->save()) {
                var_dump($cmsContentProperty->getErrors());
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $cmsContentProperty = CmsContentProperty::findOne(['code' => self::CODE, 'content_id' => PRODUCT_CONTENT_ID]);

        if ($cmsContentProperty){
            CmsContentElementProperty::deleteAll(['property_id' => $cmsContentProperty->id]);
            $cmsContentProperty->delete();
        }
    }
}