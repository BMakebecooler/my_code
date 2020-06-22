<?php

use common\models\cmsContent\CmsContentProperty;
use skeeks\cms\models\CmsContentElementProperty;
use yii\db\Migration;

class m180627_142848_add_properties_badge_text extends Migration
{
    private $propertyCodeTop = 'badge_text_top';
    private $propertyCodeBottom = 'badge_text_bottom';

    public function safeUp()
    {
        $cmsContentPropertyTop = CmsContentProperty::findOne(['code' => $this->propertyCodeTop, 'content_id' => PRODUCT_CONTENT_ID]);
        if (!$cmsContentPropertyTop){
            $cmsContentProperty = new CmsContentProperty();
            $cmsContentProperty->name = 'Текст плашки на товаре (верх)';
            $cmsContentProperty->code = $this->propertyCodeTop;
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

        $cmsContentPropertyBottom = CmsContentProperty::findOne(['code' => $this->propertyCodeBottom, 'content_id' => PRODUCT_CONTENT_ID]);
        if (!$cmsContentPropertyBottom){
            $cmsContentProperty = new CmsContentProperty();
            $cmsContentProperty->name = 'Текст плашки на товаре (низ)';
            $cmsContentProperty->code = $this->propertyCodeBottom;
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
        $cmsContentPropertyTop = CmsContentProperty::findOne(['code' => $this->propertyCodeTop]);
        if ($cmsContentPropertyTop){
            CmsContentElementProperty::deleteAll(['property_id' => $cmsContentPropertyTop->id]);
            $cmsContentPropertyTop->delete();
        }

        $cmsContentPropertyBottom = CmsContentProperty::findOne(['code' => $this->propertyCodeBottom]);
        if ($cmsContentPropertyBottom){
            CmsContentElementProperty::deleteAll(['property_id' => $cmsContentPropertyBottom->id]);
            $cmsContentPropertyBottom->delete();
        }
    }
}
