<?php

use skeeks\cms\shop\models\ShopPersonTypeProperty;
use yii\db\Migration;

class m180613_115634_add_buyer_props extends Migration
{
    private $propertyCodeKladrId = 'kladr_id';
    private $propertyCodePostalCode = 'postal_code';

    public function safeUp()
    {
        $shopPersonTypeId = 1;
        if (\Yii::$app->shop->shopPersonTypes)
        {
            $shopPersonTypeId = \Yii::$app->shop->shopPersonTypes[0]->id;
        }

        $shopPersonTypeProperty = ShopPersonTypeProperty::findOne(['code' => $this->propertyCodeKladrId]);
        if (!$shopPersonTypeProperty) {

            $shopPersonTypeProperty = new ShopPersonTypeProperty();
            $shopPersonTypeProperty->name = 'Кладр ID';
            $shopPersonTypeProperty->code = $this->propertyCodeKladrId;
            $shopPersonTypeProperty->active = \skeeks\cms\components\Cms::BOOL_Y;
            $shopPersonTypeProperty->priority = '5000';
            $shopPersonTypeProperty->property_type = 'S';
            $shopPersonTypeProperty->multiple = \skeeks\cms\components\Cms::BOOL_N;
            $shopPersonTypeProperty->component = 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText';
            $shopPersonTypeProperty->shop_person_type_id = $shopPersonTypeId;
            $shopPersonTypeProperty->is_required = \skeeks\cms\components\Cms::BOOL_N;
            $shopPersonTypeProperty->is_order_location_delivery = \skeeks\cms\components\Cms::BOOL_Y;

            if (!$shopPersonTypeProperty->save(false)) {
                var_dump($shopPersonTypeProperty->getErrors());
                var_dump($shopPersonTypeProperty->attributes);
            }
        }

        $shopPersonTypeProperty = ShopPersonTypeProperty::findOne(['code' => $this->propertyCodePostalCode]);
        if (!$shopPersonTypeProperty) {

            $shopPersonTypeProperty = new ShopPersonTypeProperty();
            $shopPersonTypeProperty->name = 'Индекс';
            $shopPersonTypeProperty->code = $this->propertyCodePostalCode;
            $shopPersonTypeProperty->active = \skeeks\cms\components\Cms::BOOL_Y;
            $shopPersonTypeProperty->priority = '6000';
            $shopPersonTypeProperty->property_type = 'S';
            $shopPersonTypeProperty->multiple = \skeeks\cms\components\Cms::BOOL_N;
            $shopPersonTypeProperty->component = 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText';
            $shopPersonTypeProperty->shop_person_type_id = $shopPersonTypeId;
            $shopPersonTypeProperty->is_required = \skeeks\cms\components\Cms::BOOL_N;
            $shopPersonTypeProperty->is_order_postcode = \skeeks\cms\components\Cms::BOOL_Y;

            if (!$shopPersonTypeProperty->save(false)) {
                var_dump($shopPersonTypeProperty->getErrors());
                var_dump($shopPersonTypeProperty->attributes);
            }
        }
    }

    public function safeDown()
    {
        ShopPersonTypeProperty::deleteAll(['code' => $this->propertyCodeKladrId]);
        ShopPersonTypeProperty::deleteAll(['code' => $this->propertyCodePostalCode]);
    }
}
