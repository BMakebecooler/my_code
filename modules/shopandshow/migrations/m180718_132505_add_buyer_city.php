<?php

use skeeks\cms\shop\models\ShopPersonTypeProperty;
use yii\db\Migration;

class m180718_132505_add_buyer_city extends Migration
{
    public function safeUp()
    {
        $shopPersonTypeId = 1;
        if (\Yii::$app->shop->shopPersonTypes)
        {
            $shopPersonTypeId = \Yii::$app->shop->shopPersonTypes[0]->id;
        }

        $shopPersonTypeProperty = ShopPersonTypeProperty::findOne(['code' => 'city']);
        if (!$shopPersonTypeProperty) {

            $shopPersonTypeProperty = new ShopPersonTypeProperty();
            $shopPersonTypeProperty->name = 'Город';
            $shopPersonTypeProperty->code = 'city';
            $shopPersonTypeProperty->active = \skeeks\cms\components\Cms::BOOL_Y;
            $shopPersonTypeProperty->priority = '3500';
            $shopPersonTypeProperty->property_type = 'S';
            $shopPersonTypeProperty->multiple = \skeeks\cms\components\Cms::BOOL_N;
            $shopPersonTypeProperty->component = 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText';
            $shopPersonTypeProperty->shop_person_type_id = $shopPersonTypeId;
            $shopPersonTypeProperty->is_required = \skeeks\cms\components\Cms::BOOL_N;
            $shopPersonTypeProperty->is_order_location_delivery = \skeeks\cms\components\Cms::BOOL_N;

            if (!$shopPersonTypeProperty->save(false)) {
                var_dump($shopPersonTypeProperty->getErrors());
                var_dump($shopPersonTypeProperty->attributes);
            }
        }

        $shopPersonTypeProperty = ShopPersonTypeProperty::findOne(['code' => 'city_kladr_id']);
        if (!$shopPersonTypeProperty) {

            $shopPersonTypeProperty = new ShopPersonTypeProperty();
            $shopPersonTypeProperty->name = 'Кладр Города';
            $shopPersonTypeProperty->code = 'city_kladr_id';
            $shopPersonTypeProperty->active = \skeeks\cms\components\Cms::BOOL_Y;
            $shopPersonTypeProperty->priority = '5000';
            $shopPersonTypeProperty->property_type = 'S';
            $shopPersonTypeProperty->multiple = \skeeks\cms\components\Cms::BOOL_N;
            $shopPersonTypeProperty->component = 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText';
            $shopPersonTypeProperty->shop_person_type_id = $shopPersonTypeId;
            $shopPersonTypeProperty->is_required = \skeeks\cms\components\Cms::BOOL_N;
            $shopPersonTypeProperty->is_order_location_delivery = \skeeks\cms\components\Cms::BOOL_N;

            if (!$shopPersonTypeProperty->save(false)) {
                var_dump($shopPersonTypeProperty->getErrors());
                var_dump($shopPersonTypeProperty->attributes);
            }
        }
    }

    public function safeDown()
    {
        ShopPersonTypeProperty::deleteAll(['code' => 'city']);
        ShopPersonTypeProperty::deleteAll(['code' => 'city_kladr_id']);
    }
}
