<?php

use skeeks\cms\components\Cms;
use yii\db\Migration;

class m171120_100053_add_new_cms_content_landing_21_11 extends Migration
{

    const CONTENT_CODE_PROMO = 'promo';
    const LANDING_PRODUCT_SECTION = 'Страница лэндинга товара';

    const CONTENT_CODE_PROMO_LANDING_PRODUCTS = 'landing-products';
    const TREE_DIR = 'promo/products';

    protected $properties = [
        'ProductId' => [
            'name' => 'Ид товара',
            'property_type' => 'S',
            'list_type' => 'L',
            'is_required' => Cms::BOOL_Y,
            'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText',
            'component_settings' => 'a:8:{s:4:"code";s:1:"S";s:4:"name";s:10:"Текст";s:13:"default_value";s:0:"";
            s:12:"fieldElement";s:9:"textInput";s:4:"rows";s:1:"5";s:2:"id";s:59:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText";
            s:8:"property";a:15:{s:6:"active";s:1:"Y";s:8:"priority";s:3:"500";s:13:"property_type";s:1:"S";s:9:"list_type";
            s:1:"L";s:8:"multiple";s:1:"N";s:10:"searchable";s:1:"N";s:9:"filtrable";s:1:"N";s:7:"version";i:1;s:15:"smart_filtrable";
            s:1:"N";s:11:"is_required";s:0:"";s:4:"name";s:38:"Ид товара";s:4:"code";s:8:"ProductId";s:9:"component";
            s:59:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText";s:4:"hint";s:0:"";s:10:"content_id";
            s:3:"199";}s:10:"activeForm";N;}',
        ],
    ];

    public function safeUp()
    {
        $tree = \skeeks\cms\models\Tree::findOne(['code' => self::CONTENT_CODE_PROMO, 'pid' => 1]);

        $landingSection = new \common\models\Tree();
        $landingSection->name = self::LANDING_PRODUCT_SECTION;
        $landingSection->code = 'landings';
        $landingSection->dir = self::TREE_DIR;
        $landingSection->tree_type_id = 2;
        $landingSection->view_file = '@template/modules/cms/tree/promo/landing-product/21.11.17.php';
        $landingSection->pid = $tree->id;
        $landingSection->pids = '1/' . $tree->id;
        $landingSection->level = 2;

        if (!$landingSection->save()) {
            var_dump($landingSection->getErrors());
            return false;
        }

        $langingContentType = \skeeks\cms\models\CmsContentType::findOne(['code' => self::CONTENT_CODE_PROMO]);

        if (!$langingContentType) {
            throw new Exception('Failed promo');
        }

        $landingProductContent = \skeeks\cms\models\CmsContent::findOne(['code' => self::CONTENT_CODE_PROMO_LANDING_PRODUCTS, 'content_type' => self::CONTENT_CODE_PROMO]);

        if (!$landingProductContent) {
            $landingProductContent = new \skeeks\cms\models\CmsContent([
                'name' => self::LANDING_PRODUCT_SECTION,
                'code' => self::CONTENT_CODE_PROMO_LANDING_PRODUCTS,
                'active' => Cms::BOOL_Y,
                'priority' => 2000,
                'name_meny' => 'Лендинг',
                'name_one' => 'Лендинг',
                'content_type' => self::CONTENT_CODE_PROMO,
                'default_tree_id' => $landingSection->id,
                'is_allow_change_tree' => Cms::BOOL_N,
                'root_tree_id' => $landingSection->id,
                'viewFile' => '@template/modules/cms/tree/promo/landing-product/21.11.17.php',
                'access_check_element' => Cms::BOOL_N
            ]);

            if (!$landingProductContent->save()) {
                throw new Exception('Failed create new landingProductContent');
            }
        }

        foreach ($this->properties as $propertyCode => $property) {

            $cmsContentProperty = \common\models\cmsContent\CmsContentProperty::find()
                ->where(['content_id' => $landingProductContent->id])
                ->andWhere(['code' => $propertyCode])
                ->limit(1)
                ->one();

            if (!$cmsContentProperty) {
                $cmsContentProperty = new \common\models\cmsContent\CmsContentProperty();
                $cmsContentProperty->code = $propertyCode;
                $cmsContentProperty->name = $property['name'];
                $cmsContentProperty->property_type = $property['property_type'];
                $cmsContentProperty->list_type = $property['list_type'];
                $cmsContentProperty->component = $property['component'];
                $cmsContentProperty->component_settings = $property['component_settings'];
                $cmsContentProperty->is_required = $property['is_required'];
                $cmsContentProperty->content_id = $landingProductContent->id;

                $cmsContentProperty->save();
            }
        }
    }

    public function safeDown()
    {
        \common\models\Tree::deleteAll("dir = :dir", [':dir' => self::TREE_DIR]);

        $landingProductContent = \skeeks\cms\models\CmsContent::findOne([
            'code' => self::CONTENT_CODE_PROMO_LANDING_PRODUCTS,
            'content_type' => self::CONTENT_CODE_PROMO
        ]);

        if (!$landingProductContent) {
            return true;
        }

        foreach ($this->properties as $propertyCode => $property) {
            $cmsContentProperty = \common\models\cmsContent\CmsContentProperty::find()
                ->where(['content_id' => $landingProductContent->id])
                ->andWhere(['code' => $propertyCode])
                ->limit(1)
                ->one();

            if ($cmsContentProperty) $cmsContentProperty->delete();
        }

        $landingProductContent->delete();
    }
}
