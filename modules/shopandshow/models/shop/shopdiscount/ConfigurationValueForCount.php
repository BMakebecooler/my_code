<?php
namespace modules\shopandshow\models\shop\shopdiscount;

use modules\shopandshow\models\shop\ShopBasket;
use yii\db\Exception;

/**
 * Расширение конфигурации для условия "Количество товаров в корзине"
 */
class ConfigurationValueForCount extends ConfigurationValue
{
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shop_discount_configuration_id' => 'ID конфигурации',
            'value' => 'Количество товаров в корзине'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getLinkedValue()
    {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function formatOutput($value)
    {
        return $value.' шт.';
    }

    /**
     * Сравнивает кол-во товаров в корзине с заданным в условии
     * @inheritdoc
     */
    public static function validateCondition(Configuration $configuration, ShopBasket $shopBasket)
    {
        $basketCount = $shopBasket->fuser->shopBasketsWithoutGifts->count();
        $configurationCount = $configuration->getValues()->one()->value;

        return $basketCount >= $configurationCount;
    }
}
