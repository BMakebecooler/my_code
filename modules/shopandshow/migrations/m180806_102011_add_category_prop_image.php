<?php

use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsTreeTypeProperty;
use yii\db\Migration;

class m180806_102011_add_category_prop_image extends Migration
{
    const TREE_TYPE_ID = 5;


    protected $properties = [
        'catalogLogo' => [
            'name' => 'Логотип раздела',
            'property_type' => 'S',
            'list_type' => 'L',
            'searchable' => Cms::BOOL_N,
            'is_required' => Cms::BOOL_N,
            'tree_type_id' => self::TREE_TYPE_ID,
            'component' => 'skeeks\cms\relatedProperties\userPropertyTypes\UserPropertyTypeSelectFile',
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
                //$cmsTreeProperty->component_settings = $property['component_settings'];
                $cmsTreeProperty->is_required = $property['is_required'];
                $cmsTreeProperty->searchable = $property['searchable'];
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
