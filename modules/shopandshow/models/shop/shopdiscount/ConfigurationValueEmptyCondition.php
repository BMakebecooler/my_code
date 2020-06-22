<?php
namespace modules\shopandshow\models\shop\shopdiscount;

use modules\shopandshow\models\shop\ShopBasket;

/**
 * Расширение конфигурации для условия "Количество товаров в корзине"
 */
class ConfigurationValueEmptyCondition extends ConfigurationValue
{
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shop_discount_configuration_id' => 'ID конфигурации',
            'value' => ''
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
        return $value;
    }

    /**
     * Всегда возвращает true
     * @inheritdoc
     */
    public static function validateCondition(Configuration $configuration, ShopBasket $shopBasket)
    {
        return true;
    }
}
