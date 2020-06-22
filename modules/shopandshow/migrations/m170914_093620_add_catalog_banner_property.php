<?php

use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsTreeTypeProperty;
use yii\db\Migration;

class m170914_093620_add_catalog_banner_property extends Migration
{

    const TREE_TYPE_ID = 5;


    protected $properties = [
        'catalogBanner' => [
            'name' => 'Баннер в каталоге',
            'property_type' => 'S',
            'list_type' => 'L',
            'searchable' => Cms::BOOL_N,
            'is_required' => Cms::BOOL_N,
            'tree_type_id' => self::TREE_TYPE_ID,
            'component' => 'skeeks\cms\relatedProperties\userPropertyTypes\UserPropertyTypeSelectFile',
            'component_settings' => 'a:5:{s:4:"code";s:1:"S";s:4:"name";s:44:"Стандартный выбор файла";s:2:"id";s:73:"skeeks\cms\relatedProperties\userPropertyTypes\UserPropertyTypeSelectFile";s:8:"property";a:23:{s:2:"id";i:18;s:10:"created_by";i:1;s:10:"updated_by";i:1;s:10:"created_at";i:1505382132;s:10:"updated_at";i:1505382132;s:4:"name";s:32:"Баннер в каталоге";s:4:"code";s:13:"catalogBanner";s:6:"active";s:1:"Y";s:8:"priority";s:3:"500";s:13:"property_type";s:1:"S";s:9:"list_type";s:1:"L";s:8:"multiple";s:1:"N";s:12:"multiple_cnt";N;s:16:"with_description";s:1:"N";s:10:"searchable";s:1:"N";s:9:"filtrable";s:1:"N";s:11:"is_required";s:1:"N";s:7:"version";i:1;s:9:"component";s:73:"skeeks\cms\relatedProperties\userPropertyTypes\UserPropertyTypeSelectFile";s:18:"component_settings";a:5:{s:4:"code";s:1:"F";s:4:"name";s:8:"Файл";s:2:"id";s:59:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeFile";s:8:"property";a:15:{s:6:"active";s:1:"Y";s:8:"priority";s:3:"500";s:13:"property_type";s:1:"S";s:9:"list_type";s:1:"L";s:8:"multiple";s:1:"N";s:10:"searchable";s:1:"N";s:9:"filtrable";s:1:"N";s:7:"version";i:1;s:15:"smart_filtrable";s:1:"N";s:11:"is_required";s:1:"N";s:4:"name";s:32:"Баннер в каталоге";s:4:"code";s:13:"catalogBanner";s:9:"component";s:59:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeFile";s:4:"hint";s:0:"";s:12:"tree_type_id";s:1:"5";}s:10:"activeForm";N;}s:4:"hint";s:0:"";s:15:"smart_filtrable";s:1:"N";s:12:"tree_type_id";s:1:"5";}s:10:"activeForm";N;}',
        ],
        'catalogBannerLink' => [
            'name' => 'Ссылка для баннера в каталоге',
            'property_type' => 'S',
            'list_type' => 'L',
            'searchable' => Cms::BOOL_N,
            'is_required' => Cms::BOOL_N,
            'tree_type_id' => self::TREE_TYPE_ID,
            'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText',
            'component_settings' => 'a:8:{s:4:"code";s:1:"S";s:4:"name";s:10:"Текст";s:13:"default_value";s:0:"";s:12:"fieldElement";s:9:"textInput";s:4:"rows";s:1:"5";s:2:"id";s:59:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText";s:8:"property";a:15:{s:6:"active";s:1:"Y";s:8:"priority";s:3:"500";s:13:"property_type";s:1:"S";s:9:"list_type";s:1:"L";s:8:"multiple";s:1:"N";s:10:"searchable";s:1:"N";s:9:"filtrable";s:1:"N";s:7:"version";i:1;s:15:"smart_filtrable";s:1:"N";s:11:"is_required";s:0:"";s:4:"name";s:43:"Ссылка для баннера в каталоге";s:4:"code";s:14:"catalogBannerLink";s:9:"component";s:59:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText";s:4:"hint";s:0:"";s:12:"tree_type_id";s:1:"5";}s:10:"activeForm";N;}',
        ],
    ];

    /**
     * @return bool
     */
    public function safeUp()
    {
        foreach ($this->properties as $propertyCode => $property) {

            $cmsTreeProperty = CmsTreeTypeProperty::find()
                ->andWhere(['code' => $propertyCode])
                ->limit(1)
                ->one();

            if (!$cmsTreeProperty) {
                $cmsTreeProperty = new CmsTreeTypeProperty();
                $cmsTreeProperty->code = $propertyCode;
                $cmsTreeProperty->name = $property['name'];
                $cmsTreeProperty->property_type = $property['property_type'];
                $cmsTreeProperty->list_type = $property['list_type'];
                $cmsTreeProperty->component = $property['component'];
                $cmsTreeProperty->component_settings = $property['component_settings'];
                $cmsTreeProperty->is_required = $property['is_required'];
                $cmsTreeProperty->tree_type_id = $property['tree_type_id'];

                $cmsTreeProperty->save();
            }
        }
    }

    public function safeDown()
    {
        CmsTreeTypeProperty::deleteAll(['code' => array_keys($this->properties)]);
    }
}
