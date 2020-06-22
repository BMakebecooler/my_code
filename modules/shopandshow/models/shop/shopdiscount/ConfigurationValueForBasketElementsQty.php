<?php
namespace modules\shopandshow\models\shop\shopdiscount;

use modules\shopandshow\models\shop\ShopBasket;
use yii\db\Exception;

/**
 * Расширение конфигурации для условия "Скидка на наименьшую сумму позиции корзины"
 */
class ConfigurationValueForBasketElementsQty extends ConfigurationValue
{
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shop_discount_configuration_id' => 'ID конфигурации',
            'value' => 'Количество позиций в корзине'
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
     * Сравнивает кол-во позиций в корзине с заданным в условии
     * @inheritdoc
     */
    public static function validateCondition(Configuration $configuration, ShopBasket $shopBasket)
    {
        $shopBaskets = $shopBasket->fuser->shopBasketsWithoutGifts;
        $basketItemsQuantity = array_sum(\common\helpers\ArrayHelper::getColumn($shopBaskets, 'quantity'));
        $configurationCount = $configuration->getValues()->one()->value;

        return $basketItemsQuantity >= $configurationCount;
    }
}
