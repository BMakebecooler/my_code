<?php
namespace modules\shopandshow\models\shop\shopdiscount;

use modules\shopandshow\models\shop\ShopBasket;
use yii\db\Exception;

/**
 * Расширение конфигурации для условия "Количество товаров в корзине"
 */
class ConfigurationValueForPromoCode extends ConfigurationValue
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
     * Проверяет, введен ли промокод от этой акции
     * @inheritdoc
     */
    public static function validateCondition(Configuration $configuration, ShopBasket $shopBasket)
    {
        $discountCoupons = $shopBasket->fuser->discountCoupons;
        foreach ($discountCoupons as $discountCoupon) {
            if($discountCoupon->shopDiscount->id == $configuration->shopDiscount->id) return true;
        }
        return false;
    }
}
