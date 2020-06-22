<?php

/**
 * php ./yii migrate/up --migrationPath=@modules/shopandshow/migrations
 */

use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsUserUniversalProperty;
use yii\db\Migration;

class m170317_075548_add_additional_properties_fot_user extends Migration
{
    protected $properties = [
        'surname' => [
            'name' => 'Фамилия',
            'property_type' => 'S',
            'list_type' => 'L',
            'is_required' => Cms::BOOL_Y,
            'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText',
            'component_settings' => 'a:8:{s:4:"code";s:1:"S";s:4:"name";s:10:"Текст";s:13:"default_value";s:0:"";s:12:"fieldElement";s:9:"textInput";s:4:"rows";s:1:"5";s:2:"id";s:59:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText";s:8:"property";a:14:{s:6:"active";s:1:"Y";s:8:"priority";s:3:"500";s:13:"property_type";s:1:"S";s:9:"list_type";s:1:"L";s:8:"multiple";s:1:"N";s:10:"searchable";s:1:"N";s:9:"filtrable";s:1:"N";s:7:"version";i:1;s:15:"smart_filtrable";s:1:"N";s:11:"is_required";s:1:"Y";s:4:"name";s:14:"Фамилия";s:4:"code";s:7:"surname";s:9:"component";s:59:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText";s:4:"hint";s:0:"";}s:10:"activeForm";N;}',
        ],

        'isSubscribe' => [
            'name' => 'Подписаться на новости',
            'property_type' => 'N',
            'list_type' => 'L',
            'is_required' => Cms::BOOL_Y,
            'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeNumber',
            'component_settings' => 'a:6:{s:4:"code";s:1:"N";s:4:"name";s:10:"Число";s:13:"default_value";s:1:"0";s:2:"id";s:61:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeNumber";s:8:"property";a:14:{s:6:"active";s:1:"Y";s:8:"priority";s:3:"500";s:13:"property_type";s:1:"S";s:9:"list_type";s:1:"L";s:8:"multiple";s:1:"N";s:10:"searchable";s:1:"N";s:9:"filtrable";s:1:"N";s:7:"version";i:1;s:15:"smart_filtrable";s:1:"N";s:11:"is_required";s:1:"N";s:4:"name";s:42:"Подписаться на новости";s:4:"code";s:11:"isSubscribe";s:9:"component";s:61:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeNumber";s:4:"hint";s:0:"";}s:10:"activeForm";N;}',

        ],
    ];
    
    public function safeUp()
    {
        foreach ($this->properties as $propertyCode => $property){

            $cmsUserProperty = CmsUserUniversalProperty::find()
                ->andWhere(['code' => $propertyCode])
                ->limit(1)
                ->one();

            if (!$cmsUserProperty) {
                $cmsUserProperty = new CmsUserUniversalProperty();
                $cmsUserProperty->code = $propertyCode;
                $cmsUserProperty->name = $property['name'];
                $cmsUserProperty->property_type = $property['property_type'];
                $cmsUserProperty->list_type = $property['list_type'];
                $cmsUserProperty->component = $property['component'];
                $cmsUserProperty->component_settings = $property['component_settings'];
                $cmsUserProperty->is_required = $property['is_required'];

                $cmsUserProperty->save();
            }
        }
        
        return true;
    }

    public function safeDown()
    {
        $result = CmsUserUniversalProperty::deleteAll(['code' => array_keys($this->properties)]);

        return true;
    }

}
