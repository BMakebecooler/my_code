<?php

use yii\db\Migration;

class m170920_131052_add_promo_pages extends Migration
{
    protected $properties = [
        /*
        'activefrom' => [
            'name' => 'Активен с',
            'property_type' => 'N',
            'list_type' => 'L',
            'is_required' => \skeeks\cms\components\Cms::BOOL_Y,
            'component' => 'skeeks\cms\relatedProperties\userPropertyTypes\UserPropertyTypeDate',
            'component_settings' => 'a:6:{s:4:"code";s:1:"N";s:4:"name";s:22:"Дата и время";s:4:"type";s:8:"datetime";s:2:"id";s:67:"skeeks\cms\relatedProperties\userPropertyTypes\UserPropertyTypeDate";s:8:"property";a:27:{s:2:"id";i:180;s:10:"created_by";i:1000;s:10:"updated_by";i:1000;s:10:"created_at";i:1505916031;s:10:"updated_at";i:1505916031;s:4:"name";s:17:"Активен с";s:4:"code";s:10:"activefrom";s:10:"content_id";s:3:"168";s:6:"active";s:1:"Y";s:8:"priority";s:3:"500";s:13:"property_type";s:1:"N";s:9:"list_type";s:1:"L";s:8:"multiple";s:1:"N";s:12:"multiple_cnt";N;s:16:"with_description";s:1:"N";s:10:"searchable";s:1:"N";s:9:"filtrable";s:1:"N";s:11:"is_required";s:1:"Y";s:7:"version";i:1;s:9:"component";s:67:"skeeks\cms\relatedProperties\userPropertyTypes\UserPropertyTypeDate";s:18:"component_settings";a:6:{s:4:"code";s:1:"N";s:4:"name";s:22:"Дата и время";s:4:"type";s:8:"datetime";s:2:"id";s:67:"skeeks\cms\relatedProperties\userPropertyTypes\UserPropertyTypeDate";s:8:"property";a:15:{s:6:"active";s:1:"Y";s:8:"priority";s:3:"500";s:13:"property_type";s:1:"S";s:9:"list_type";s:1:"L";s:8:"multiple";s:1:"N";s:10:"searchable";s:1:"N";s:9:"filtrable";s:1:"N";s:7:"version";i:1;s:15:"smart_filtrable";s:1:"N";s:11:"is_required";s:1:"Y";s:4:"name";s:17:"Активен с";s:4:"code";s:10:"activefrom";s:9:"component";s:67:"skeeks\cms\relatedProperties\userPropertyTypes\UserPropertyTypeDate";s:4:"hint";s:0:"";s:10:"content_id";s:3:"168";}s:10:"activeForm";N;}s:4:"hint";s:0:"";s:15:"smart_filtrable";s:1:"N";s:9:"vendor_id";N;s:11:"filter_name";N;s:11:"widget_name";N;s:9:"item_name";N;}s:10:"activeForm";N;}',
        ],

        'activeto' => [
            'name' => 'Активен по',
            'property_type' => 'N',
            'list_type' => 'L',
            'is_required' => \skeeks\cms\components\Cms::BOOL_Y,
            'component' => 'skeeks\cms\relatedProperties\userPropertyTypes\UserPropertyTypeDate',
            'component_settings' => 'a:6:{s:4:"code";s:1:"N";s:4:"name";s:22:"Дата и время";s:4:"type";s:8:"datetime";s:2:"id";s:67:"skeeks\cms\relatedProperties\userPropertyTypes\UserPropertyTypeDate";s:8:"property";a:15:{s:6:"active";s:1:"Y";s:8:"priority";s:3:"500";s:13:"property_type";s:1:"S";s:9:"list_type";s:1:"L";s:8:"multiple";s:1:"N";s:10:"searchable";s:1:"N";s:9:"filtrable";s:1:"N";s:7:"version";i:1;s:15:"smart_filtrable";s:1:"N";s:11:"is_required";s:1:"Y";s:4:"name";s:19:"Активен по";s:4:"code";s:8:"activeto";s:9:"component";s:67:"skeeks\cms\relatedProperties\userPropertyTypes\UserPropertyTypeDate";s:4:"hint";s:0:"";s:10:"content_id";s:3:"168";}s:10:"activeForm";N;}',
        ],
        */
        'promourl' => [
            'name' => 'Ссылка на промоакцию',
            'property_type' => 'S',
            'list_type' => 'L',
            'is_required' => \skeeks\cms\components\Cms::BOOL_N,
            'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText',
            'component_settings' => 'a:8:{s:4:"code";s:1:"S";s:4:"name";s:10:"Текст";s:13:"default_value";s:0:"";s:12:"fieldElement";s:9:"textInput";s:4:"rows";s:1:"5";s:2:"id";s:59:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText";s:8:"property";a:15:{s:6:"active";s:1:"Y";s:8:"priority";s:3:"500";s:13:"property_type";s:1:"S";s:9:"list_type";s:1:"L";s:8:"multiple";s:1:"N";s:10:"searchable";s:1:"N";s:9:"filtrable";s:1:"N";s:7:"version";i:1;s:15:"smart_filtrable";s:1:"N";s:11:"is_required";s:0:"";s:4:"name";s:38:"Ссылка на промоакцию";s:4:"code";s:8:"promourl";s:9:"component";s:59:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText";s:4:"hint";s:0:"";s:10:"content_id";s:3:"169";}s:10:"activeForm";N;}',
        ],
        'promorules' => [
            'name' => 'Правила акции',
            'property_type' => 'S',
            'list_type' => 'L',
            'is_required' => \skeeks\cms\components\Cms::BOOL_N,
            'component' => 'skeeks\cms\relatedProperties\userPropertyTypes\UserPropertyTypeSelectFile',
            'component_settings' => 'a:5:{s:4:"code";s:1:"S";s:4:"name";s:44:"Стандартный выбор файла";s:2:"id";s:73:"skeeks\cms\relatedProperties\userPropertyTypes\UserPropertyTypeSelectFile";s:8:"property";a:15:{s:6:"active";s:1:"Y";s:8:"priority";s:3:"500";s:13:"property_type";s:1:"S";s:9:"list_type";s:1:"L";s:8:"multiple";s:1:"N";s:10:"searchable";s:1:"N";s:9:"filtrable";s:1:"N";s:7:"version";i:1;s:15:"smart_filtrable";s:1:"N";s:11:"is_required";s:0:"";s:4:"name";s:25:"Правила акции";s:4:"code";s:10:"promorules";s:9:"component";s:73:"skeeks\cms\relatedProperties\userPropertyTypes\UserPropertyTypeSelectFile";s:4:"hint";s:0:"";s:10:"content_id";s:3:"169";}s:10:"activeForm";N;}',
        ],
    ];

    public function safeUp()
    {
        // фикс ссылки /promo (добавляем view)
        $this->execute("update cms_tree set view_file = 'promo' where code = 'promo'");
        // переименуем описания страниц
        $this->execute("update cms_content set name_meny = 'Промоакции', name_one = 'Промоакция' where code = 'promo'");

        $promoTree = \skeeks\cms\models\Tree::findOne(['code' => 'promo', 'pid' => 1]);
        
        $promoContentType = \skeeks\cms\models\CmsContentType::findOne(['code' => 'promo']);
        if(!$promoContentType) {
            $promoContentType = new \skeeks\cms\models\CmsContentType([
                'priority' => 500,
                'name' => 'Промоакции',
                'code' => 'promo'
            ]);
            if (!$promoContentType->save()) {
                throw new Exception('Failed create new promoContentType');
            }
        }

        $promoPagesContent = \skeeks\cms\models\CmsContent::findOne(['code' => 'actions', 'content_type' => 'promo']);
        if(!$promoPagesContent) {
            $promoPagesContent = new \skeeks\cms\models\CmsContent([
                'name' => 'Акции',
                'code' => 'actions',
                'active' => \skeeks\cms\components\Cms::BOOL_Y,
                'priority' => 2000,
                'name_meny' => 'Акции',
                'name_one' => 'Акция',
                'content_type' => 'promo',
                'default_tree_id' => $promoTree->id,
                'is_allow_change_tree' => \skeeks\cms\components\Cms::BOOL_N,
                'root_tree_id' => $promoTree->id,
                'viewFile' => '@template/modules/cms/content-element/action',
                'access_check_element' => \skeeks\cms\components\Cms::BOOL_N
            ]);
            if (!$promoPagesContent->save()) {
                throw new Exception('Failed create new promoPagesContent');
            }
        }

        foreach ($this->properties as $propertyCode => $property){

            $cmsContentProperty = \common\models\cmsContent\CmsContentProperty::find()
                ->where(['content_id' => $promoPagesContent->id])
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
                $cmsContentProperty->content_id = $promoPagesContent->id;

                $cmsContentProperty->save();
            }
        }
    }

    public function safeDown()
    {
        $promoPagesContent = \skeeks\cms\models\CmsContent::findOne(['code' => 'actions', 'content_type' => 'promo']);
        foreach ($this->properties as $propertyCode => $property) {
            $cmsContentProperty = \common\models\cmsContent\CmsContentProperty::find()
                ->where(['content_id' => $promoPagesContent->id])
                ->andWhere(['code' => $propertyCode])
                ->limit(1)
                ->one();

            if($cmsContentProperty) $cmsContentProperty->delete();
        }

        $promoPagesContent->delete();

        $promoContentType = \skeeks\cms\models\CmsContentType::findOne(['code' => 'promo']);
        $promoContentType->delete();
    }
}
