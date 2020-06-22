<?php

use skeeks\cms\models\CmsTreeType;
use skeeks\cms\models\CmsTreeTypeProperty;
use yii\db\Migration;

class m180627_135640_change_stock_landing extends Migration
{


    const TREE_TYPE_CODE = 'stock-landing';

    /**
     * @var CmsTreeType
     */
    protected $treeType;


    public function init()
    {
        parent::init();

        $this->treeType = CmsTreeType::findOne(['code' => self::TREE_TYPE_CODE]);
    }


    public function safeUp()
    {

        $treeType = new CmsTreeType();

        $treeType->name = 'Лендинг стока';
        $treeType->code = self::TREE_TYPE_CODE;
        $treeType->viewFile = '@template/modules/cms/tree/promo/landing/stock/index.php';

        if ($treeType->save()) {

            $property = new CmsTreeTypeProperty();

            $property->name = 'Ид баннера';
            $property->code = 'ShareId';
            $property->property_type = 'N';
            $property->list_type = 'L';
            $property->tree_type_id = $treeType->id;
            $property->component = 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeNumber';
            $property->component_settings = 'a:6:{s:4:"code";s:1:"N";s:4:"name";s:10:"Число";s:13:"default_value";s:0:"";s:2:"id";s:61:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeNumber";s:8:"property";a:15:{s:6:"active";s:1:"Y";s:8:"priority";s:3:"500";s:13:"property_type";s:1:"S";s:9:"list_type";s:1:"L";s:8:"multiple";s:1:"N";s:10:"searchable";s:1:"N";s:9:"filtrable";s:1:"N";s:7:"version";i:1;s:15:"smart_filtrable";s:1:"N";s:11:"is_required";s:1:"N";s:4:"name";s:19:"Ид баннера";s:4:"code";s:7:"ShareId";s:9:"component";s:61:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeNumber";s:4:"hint";s:0:"";s:12:"tree_type_id";s:1:"' . $treeType->id . '";}s:10:"activeForm";N;}';

            if (!$property->save()) {
                var_dump($property->getErrors());
                die();

            }
        } else {
            var_dump($treeType->getErrors());
        }

    }

    public function safeDown()
    {
        CmsTreeTypeProperty::deleteAll("code = :code AND tree_type_id = :tree_type_id", [':code' => 'ShareId', ':tree_type_id' => $this->treeType->id]);

        if ($this->treeType) {
            $this->treeType->delete();
        }
    }

}
