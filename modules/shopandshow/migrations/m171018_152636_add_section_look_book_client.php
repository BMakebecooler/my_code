<?php

use yii\db\Migration;

class m171018_152636_add_section_look_book_client extends Migration
{
    const CONTENT_CODE_LOOKBOOK = 'lookbook';
    const CONTENT_CODE_LOOKBOOK_CLIENTS = 'lookbook-clients';
    const LOOKBOOK_NAME = 'Страница конкурса "Лукбук клиента"';

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
        'city' => [
            'name' => 'Город',
            'property_type' => 'S',
            'list_type' => 'L',
            'is_required' => \skeeks\cms\components\Cms::BOOL_N,
            'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText',
            'component_settings' => 'a:8:{s:4:"code";s:1:"S";s:4:"name";s:10:"Текст";s:13:"default_value";s:0:"";
            s:12:"fieldElement";s:9:"textInput";s:4:"rows";s:1:"5";s:2:"id";s:59:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText";
            s:8:"property";a:15:{s:6:"active";s:1:"Y";s:8:"priority";s:3:"500";s:13:"property_type";s:1:"S";s:9:"list_type";
            s:1:"L";s:8:"multiple";s:1:"N";s:10:"searchable";s:1:"N";s:9:"filtrable";s:1:"N";s:7:"version";i:1;s:15:"smart_filtrable";
            s:1:"N";s:11:"is_required";s:0:"";s:4:"name";s:38:"Город";s:4:"code";s:8:"city";
            s:9:"component";s:59:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText";s:4:"hint";s:0:"";s:10:"content_id";
            s:3:"169";}s:10:"activeForm";N;}',
        ],
        'countLike' => [
            'name' => 'Кол-во лайков',
            'property_type' => 'S',
            'list_type' => 'L',
            'is_required' => \skeeks\cms\components\Cms::BOOL_N,
            'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText',
            'component_settings' => 'a:8:{s:4:"code";s:1:"S";s:4:"name";s:10:"Текст";s:13:"default_value";s:0:"";
            s:12:"fieldElement";s:9:"textInput";s:4:"rows";s:1:"5";s:2:"id";s:59:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText";
            s:8:"property";a:15:{s:6:"active";s:1:"Y";s:8:"priority";s:3:"500";s:13:"property_type";s:1:"S";s:9:"list_type";
            s:1:"L";s:8:"multiple";s:1:"N";s:10:"searchable";s:1:"N";s:9:"filtrable";s:1:"N";s:7:"version";i:1;s:15:"smart_filtrable";
            s:1:"N";s:11:"is_required";s:0:"";s:4:"name";s:38:"Кол-во лайков";s:4:"code";s:8:"count_like";s:9:"component";
            s:59:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText";s:4:"hint";s:0:"";s:10:"content_id";
            s:3:"169";}s:10:"activeForm";N;}',
        ],
    ];

    public function safeUp()
    {
        $tree = \skeeks\cms\models\Tree::findOne(['code' => self::CONTENT_CODE_LOOKBOOK, 'pid' => 1]);

        $lookbookSection = new \common\models\Tree();
        $lookbookSection->name = self::LOOKBOOK_NAME;
        $lookbookSection->code = 'clients';
        $lookbookSection->dir = 'lookbook/clients';
        $lookbookSection->tree_type_id = 2;
        $lookbookSection->view_file = '@template/modules/cms/content-element/lookbook/clients';
        $lookbookSection->pid = $tree->id;
        $lookbookSection->pids = '1/'.$tree->id;
        $lookbookSection->level = 2;

        if (!$lookbookSection->save()) {
            var_dump($lookbookSection->getErrors());
            return false;
        }

        $lookbookContentType = \skeeks\cms\models\CmsContentType::findOne(['code' => self::CONTENT_CODE_LOOKBOOK]);

        if (!$lookbookContentType) {
            throw new Exception('Failed lookbookContentType');
        }

        $lookbookClientContent = \skeeks\cms\models\CmsContent::findOne(['code' => self::CONTENT_CODE_LOOKBOOK_CLIENTS, 'content_type' => 'lookbook']);

        if (!$lookbookClientContent) {
            $lookbookClientContent = new \skeeks\cms\models\CmsContent([
                'name' => self::LOOKBOOK_NAME,
                'code' => self::CONTENT_CODE_LOOKBOOK_CLIENTS,
                'active' => \skeeks\cms\components\Cms::BOOL_Y,
                'priority' => 2000,
                'name_meny' => 'Клиенты',
                'name_one' => 'Клиент',
                'content_type' => self::CONTENT_CODE_LOOKBOOK,
                'default_tree_id' => $lookbookSection->id,
                'is_allow_change_tree' => \skeeks\cms\components\Cms::BOOL_N,
                'root_tree_id' => $lookbookSection->id,
                'viewFile' => '@template/modules/cms/content-element/lookbook/clients',
                'access_check_element' => \skeeks\cms\components\Cms::BOOL_N
            ]);

            if (!$lookbookClientContent->save()) {
                throw new Exception('Failed create new lookbookClientContent');
            }
        }

        foreach ($this->properties as $propertyCode => $property) {

            $cmsContentProperty = \common\models\cmsContent\CmsContentProperty::find()
                ->where(['content_id' => $lookbookClientContent->id])
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
                $cmsContentProperty->content_id = $lookbookClientContent->id;

                $cmsContentProperty->save();
            }
        }
    }

    public function safeDown()
    {
        \common\models\Tree::deleteAll("dir = 'lookbook/clients'");

        $lookbookClientContent = \skeeks\cms\models\CmsContent::findOne([
            'code' => self::CONTENT_CODE_LOOKBOOK_CLIENTS,
            'content_type' => self::CONTENT_CODE_LOOKBOOK
        ]);


        if (!$lookbookClientContent) {
            return true;
        }

        foreach ($this->properties as $propertyCode => $property) {
            $cmsContentProperty = \common\models\cmsContent\CmsContentProperty::find()
                ->where(['content_id' => $lookbookClientContent->id])
                ->andWhere(['code' => $propertyCode])
                ->limit(1)
                ->one();

            if ($cmsContentProperty) $cmsContentProperty->delete();
        }

        $lookbookClientContent->delete();
    }
}
