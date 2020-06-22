<?php

use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentProperty;
use yii\db\Migration;

class m180405_101231_add_winners_content_and_props extends Migration
{
    const CONTENT_CODE = 'winners';

    public $props = [
        ['name' => 'ФИО', 'code' => 'winner_fio'],
        ['name' => 'Телефон', 'code' => 'winner_phone'],
        ['name' => 'Заказ', 'code' => 'winner_order_id'],
        ['name' => 'Приз', 'code' => 'winner_product'],
        ['name' => 'Город', 'code' => 'winner_city'],
    ];

    public function safeUp()
    {
        $cmsContent = CmsContent::findOne(['code' => self::CONTENT_CODE]);
        if (!$cmsContent) {

            $cmsContent = new CmsContent([
                'name' => 'Победители розыгрыша призов апреля 2018',
                'code' => self::CONTENT_CODE,
                'active' => \skeeks\cms\components\Cms::BOOL_Y,
                'priority' => 500,
                'name_meny' => 'Победители',
                'name_one' => 'Победитель',
                'content_type' => 'promo',
                'default_tree_id' => null,
                'is_allow_change_tree' => \skeeks\cms\components\Cms::BOOL_Y,
                'root_tree_id' => null,
                'viewFile' => null,
                'access_check_element' => \skeeks\cms\components\Cms::BOOL_N,
            ]);

            if (!$cmsContent->save()) {
                var_dump($cmsContent->getErrors());
            }
        }

        foreach ($this->props as $prop) {
            $cmsContentProperty = CmsContentProperty::findOne(['code' => $prop['code'], 'content_id' => $cmsContent->id]);
            if (!$cmsContentProperty) {

                $cmsContentProperty = new CmsContentProperty();
                $cmsContentProperty->name = $prop['name'];
                $cmsContentProperty->code = $prop['code'];
                $cmsContentProperty->content_id = $cmsContent->id;
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
    }

    public function safeDown()
    {
        $cmsContent = CmsContent::findOne(['code' => self::CONTENT_CODE]);
        if (!$cmsContent) return;

        CmsContentProperty::deleteAll(['content_id' => $cmsContent->id]);
        $cmsContent->delete();
    }
}
