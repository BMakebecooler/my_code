<?php

use yii\db\Migration;

class m171101_120037_cms_tree_type_add_products extends Migration
{
    public function safeUp()
    {
        $component_settings = 'a:7:{s:9:"enumRoute";s:35:"cms/admin-cms-content-property-enum";s:4:"code";s:1:"L";s:4:"name";s:12:"Список";s:12:"fieldElement";s:7:"listbox";s:2:"id";s:59:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeList";s:8:"property";a:15:{s:6:"active";s:1:"Y";s:8:"priority";s:3:"500";s:13:"property_type";s:1:"L";s:9:"list_type";s:1:"L";s:8:"multiple";s:1:"Y";s:10:"searchable";s:1:"N";s:9:"filtrable";s:1:"N";s:7:"version";i:1;s:15:"smart_filtrable";s:1:"N";s:11:"is_required";s:1:"N";s:4:"name";s:35:"Связанные продукты";s:4:"code";s:8:"products";s:9:"component";s:59:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeList";s:4:"hint";s:0:"";s:12:"tree_type_id";s:1:"5";}s:10:"activeForm";N;}';

        $query = <<<SQL
INSERT INTO cms_tree_type_property(name, code, active, priority, property_type, list_type, multiple, with_description, searchable, filtrable, 
  is_required, version, component, component_settings, smart_filtrable, tree_type_id)
VALUES ('Связанные продукты', 'savedProducts', 'Y', 500, 'L', 'L', 'Y', 'N', 'N', 'N',
  'N', 1, :component, :component_settings, 'N', 5);
SQL;

        $this->db->createCommand($query,
            [':component' => 'skeeks\\cms\\relatedProperties\\propertyTypes\\PropertyTypeList', ':component_settings' => $component_settings]
        )->execute();
    }

    public function safeDown()
    {
        $cmsTreeTypeProperty = \skeeks\cms\models\CmsTreeTypeProperty::find()->where('code = "savedProducts"')->andWhere('tree_type_id = "'.CATALOG_TREE_TYPE_ID.'"')->one();
        if($cmsTreeTypeProperty) $cmsTreeTypeProperty->delete();
    }
}
