<?php
namespace modules\shopandshow\models\shop\shopdiscount;

use modules\shopandshow\models\shop\ShopBasket;

/**
 * Расширение конфигурации для условия "Сумма товаров"
 */
class ConfigurationValueForSum extends ConfigurationValue
{
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shop_discount_configuration_id' => 'ID конфигурации',
            'value' => 'Сумма товаров'
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
        return $value.' руб.';
    }

    /**
     * Сравнивает сумму товаров в корзине с заданным условием акциии
     * @inheritdoc
     */
    public static function validateCondition(Configuration $configuration, ShopBasket $shopBasket)
    {
        static $values = [];
        static $basketSum = null;

        if ($basketSum === null) {
            $money = \Yii::$app->money->newMoney();
            foreach ($shopBasket->fuser->shopBasketsWithoutGifts as $shopBasket) {
                $money = $money->add($shopBasket->moneyOriginal->multiply($shopBasket->quantity));
            }

            $basketSum = $money->getValue();
        }

        if (!array_key_exists($configuration->id, $values)) {
            $values[$configuration->id] = $configuration->getValues()->one()->value;
        }
        $configurationSum = $values[$configuration->id];

        return $basketSum >= $configurationSum;
    }
}
