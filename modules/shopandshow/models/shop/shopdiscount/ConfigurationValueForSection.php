<?php
namespace modules\shopandshow\models\shop\shopdiscount;

use common\helpers\ArrayHelper;
use modules\shopandshow\models\shop\ShopBasket;
use skeeks\cms\models\CmsTree;
use yii\helpers\VarDumper;

/**
 * Расширение конфигурации для условия "Категория"
 */
class ConfigurationValueForSection extends ConfigurationValue
{
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shop_discount_configuration_id' => 'ID конфигурации',
            'value' => 'Категория'
        ];
    }

    /**
     * @return mixed
     */
    public function getLinkedValue()
    {
        $contentElement = CmsTree::findOne($this->value);
        return $contentElement;
    }

    /**
     * @param $value CmsTree
     *
     * @return string
     */
    public function formatOutput($value)
    {
        return sprintf('[%s] %s', $value->dir, $value->name);
    }

    /**
     * Ищет заданный товар корзины в списке условий акции
     * @inheritdoc
     */
    public static function validateCondition(Configuration $configuration, ShopBasket $shopBasket)
    {
        $cmsContentElement = \common\lists\Contents::getContentElementById($shopBasket->product->id);

        $configurationTreeIds = ArrayHelper::getColumn($configuration->getValues()->asArray()->all(), 'value');

        if(in_array($cmsContentElement->tree_id, $configurationTreeIds)) return true;

        while($configurationTreeIds = CmsTree::find()->active()->where(['pid' => $configurationTreeIds])->select('id')->distinct()->column()) {
            if(in_array($cmsContentElement->tree_id, $configurationTreeIds)) return true;
        }

        return false;
    }
}
