<?php

namespace modules\shopandshow\models\shop\shopdiscount;

use common\helpers\ArrayHelper;
use common\models\cmsContent\CmsContentElement;
use modules\shopandshow\models\shop\ShopBasket;
use yii\db\Exception;

/**
 * Расширение конфигурации для условия "Лоты"
 */
class ConfigurationValueForLots extends ConfigurationValue
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

        if ($shopBasket->isGift) return false;

        if (!array_key_exists($configuration->id, $values)) {
            $values[$configuration->id] = $configuration->getValues()->asArray()->indexBy('value')->all();
        }
        $configurationProductIds = $values[$configuration->id];

        if (array_key_exists($shopBasket->product_id, $configurationProductIds)) {
            return true;
        }

        return array_key_exists($shopBasket->main_product_id, $configurationProductIds);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentElement()
    {
        return $this->hasMany(CmsContentElement::className(), ['id' => 'value']);
    }
}
