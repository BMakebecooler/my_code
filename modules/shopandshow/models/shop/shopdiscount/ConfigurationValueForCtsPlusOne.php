<?php

namespace modules\shopandshow\models\shop\shopdiscount;

use common\helpers\ArrayHelper;
use common\models\cmsContent\CmsContentElement;
use modules\shopandshow\models\shop\ShopBasket;
use modules\shopandshow\models\shop\ShopContentElement;
use yii\db\Exception;

/**
 * Расширение конфигурации для условия "цтс+лот"
 */
class ConfigurationValueForCtsPlusOne extends ConfigurationValue
{
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shop_discount_configuration_id' => 'ID конфигурации',
            'value' => 'Список лотов'
        ];
    }

    /**
     * @return mixed
     */
    public function getLinkedValue()
    {
        return \common\lists\Contents::getContentElementById($this->value);
    }

    /**
     * @param $value CmsContentElement
     *
     * @return string
     */
    public function formatOutput($value)
    {
        return sprintf('[%s] %s', $value->code, $value->name);
    }

    /**
     * Ищет заданный товар корзины в списке условий акции
     * @inheritdoc
     */
    public static function validateCondition(Configuration $configuration, ShopBasket $shopBasket)
    {
        static $values = [];
        static $ctsProduct = null;

        if ($shopBasket->isGift) return false;

        if ($ctsProduct === null) {
            $ctsProduct = \common\lists\Contents::getCtsProduct();
        }

        // цтс не найден - акция не работает
        if (!$ctsProduct) {
            return false;
        }

        if (!array_key_exists($configuration->id, $values)) {
            $values[$configuration->id] = $configuration->getValues()->asArray()->indexBy('value')->all();
        }
        $configurationProductIds = $values[$configuration->id];

        // если есть лот - проверяем наличие цтс
        if (array_key_exists($shopBasket->main_product_id, $configurationProductIds)
            || array_key_exists($shopBasket->product_id, $configurationProductIds)
        ) {
            return self::validateCts($shopBasket, $ctsProduct);
        }
        return false;
    }

    /**
     * Проверяет наличие товара цтс в корзине
     *
     * @param ShopBasket $shopBasket
     * @param ShopContentElement $ctsProduct
     * @return bool
     */
    public static function validateCts(ShopBasket $shopBasket, $ctsProduct)
    {
        // если сам лот = цтс, акция не применяется
        if ($shopBasket->main_product_id == $ctsProduct->id || $shopBasket->product_id == $ctsProduct->id) {
            return false;
        }

        foreach ($shopBasket->fuser->shopBasketsWithoutGifts as $_shopBasket) {
            if ($_shopBasket->main_product_id == $ctsProduct->id || $_shopBasket->product_id == $ctsProduct->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentElement()
    {
        return $this->hasMany(CmsContentElement::className(), ['id' => 'value']);
    }
}
