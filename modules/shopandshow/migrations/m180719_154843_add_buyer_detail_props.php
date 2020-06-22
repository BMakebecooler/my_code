<?php

use skeeks\cms\shop\models\ShopPersonTypeProperty;
use yii\db\Migration;

class m180719_154843_add_buyer_detail_props extends Migration
{
    private $props = [
        ['Region', 'Регион'],
        ['District', 'Район'],
        ['SettlementType', 'Тип местности'],
        ['SettlementName', 'Название местности'],
        ['StreetName', 'Название улицы'],
        ['StreetNumber', 'Номер дома'],
        ['BuildNumber', 'Номер строения'],
        ['DoorNumber', 'Номер квартиры'],
    ];

    public function safeUp()
    {
        $shopPersonTypeId = 1;
        if (\Yii::$app->shop->shopPersonTypes)
        {
            $shopPersonTypeId = \Yii::$app->shop->shopPersonTypes[0]->id;
        }

        foreach ($this->props as $prop) {
            $shopPersonTypeProperty = ShopPersonTypeProperty::findOne(['code' => $prop[0]]);
            if (!$shopPersonTypeProperty) {

                $shopPersonTypeProperty = new ShopPersonTypeProperty();
                $shopPersonTypeProperty->name = $prop[1];
                $shopPersonTypeProperty->code = $prop[0];
                $shopPersonTypeProperty->active = \skeeks\cms\components\Cms::BOOL_Y;
                $shopPersonTypeProperty->priority = '7000';
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
    }

    public function safeDown()
    {
        foreach ($this->props as $prop) {
            ShopPersonTypeProperty::deleteAll(['code' => $prop[0]]);
        };
    }
}
