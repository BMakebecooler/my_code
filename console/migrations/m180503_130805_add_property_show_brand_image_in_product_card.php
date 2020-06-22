<?php

use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentElementProperty;
use skeeks\cms\models\CmsContentProperty;
use skeeks\cms\models\CmsContentPropertyEnum;
use yii\db\Migration;

/**
 * Class m180503_130805_add_property_show_brand_image_in_product_card
 */
class m180503_130805_add_property_show_brand_image_in_product_card extends Migration
{

    const CONTENT_CODE = 'brand';

    public $propCode = 'show_brand_image_in_product_card';

    public function safeUp()
    {
        $cmsContent = CmsContent::findOne(['code' => self::CONTENT_CODE]);

        if ($cmsContent){
            $cmsContentProperty = CmsContentProperty::findOne(['code' => $this->propCode, 'content_id' => $cmsContent->id]);
            if (!$cmsContentProperty) {

                $cmsContentProperty = new CmsContentProperty();
                $cmsContentProperty->name = 'Показывать изображение бренда в карточке товара';
                $cmsContentProperty->code = $this->propCode;
                $cmsContentProperty->content_id = $cmsContent->id;
                $cmsContentProperty->active = \skeeks\cms\components\Cms::BOOL_Y;
                $cmsContentProperty->property_type = 'L';
                $cmsContentProperty->list_type = 'L';
                $cmsContentProperty->multiple = \skeeks\cms\components\Cms::BOOL_N;
                $cmsContentProperty->component = 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeList';
                $cmsContentProperty->is_required = \skeeks\cms\components\Cms::BOOL_N;
                $cmsContentProperty->with_description = \skeeks\cms\components\Cms::BOOL_N;

                if (!$cmsContentProperty->save(false)) {
                    var_dump($cmsContentProperty->getErrors());
                    var_dump($cmsContentProperty->attributes);
                }else{

                    $this->update(
                        CmsContentProperty::tableName() . ' AS content_property',
                        ['property_type' => 'L'],
                        ['id' => $cmsContentProperty->id]
                    );

                    $enumItems = ['Y' => 'Показывать', 'N' => 'Скрывать'];

                    foreach ($enumItems as $enumItemCode => $enumItemDescr) {
                        $cmsContentPropertyEnum = new CmsContentPropertyEnum();
                        $cmsContentPropertyEnum->property_id = $cmsContentProperty->id;
                        $cmsContentPropertyEnum->code = $enumItemCode;
                        $cmsContentPropertyEnum->value = $enumItemDescr;

                        $cmsContentPropertyEnum->save();
                    }
                }
            }
        }
    }

    public function safeDown()
    {
        $cmsContentProperty = CmsContentProperty::findOne(['code' => $this->propCode]);

        if ($cmsContentProperty){
            $res = CmsContentElementProperty::deleteAll(['property_id' => $cmsContentProperty->id]);
            CmsContentPropertyEnum::deleteAll(['property_id' => $cmsContentProperty->id]);
            $cmsContentProperty->delete();
        }
    }
}
