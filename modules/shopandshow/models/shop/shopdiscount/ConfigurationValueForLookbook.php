<?php
namespace modules\shopandshow\models\shop\shopdiscount;

use common\models\cmsContent\CmsContentProperty;
use modules\shopandshow\models\shop\ShopBasket;

/**
 * Расширение конфигурации для условия "Лукбук"
 */
class ConfigurationValueForLookbook extends ConfigurationValue
{
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shop_discount_configuration_id' => 'ID конфигурации',
            'value' => 'Лукбук'
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
     * Сравнивает сумму товаров в корзине с заданным условием акциии
     * @inheritdoc
     */
    public static function validateCondition(Configuration $configuration, ShopBasket $shopBasket)
    {
        $lookbooks = self::getFullLookBooks($shopBasket->fuser_id);

        if($lookbooks)
        foreach ($lookbooks as $lookbook) {
            $basket_ids = explode(',', $lookbook['basket_ids']);
            if(in_array($shopBasket->id, $basket_ids)) return true;
        }

        return false;
    }

    /**
     * Получает список лукбуков, полностью находящихся в корзине
     * @param $shopFuserId
     *
     * @return array
     */
    protected static function getFullLookBooks($shopFuserId) {
        static $property = null;

        // cache property value
        if(empty($property)) $property = CmsContentProperty::findOne(['code' => 'products', 'content_id' => LOOKBOOK_CONTENT_ID]);

        $sql = '
SELECT 
    ep.element_id, GROUP_CONCAT(bp.bid) basket_ids
FROM
    cms_content_element_property ep
    LEFT JOIN
      (SELECT distinct IF(ce.content_id = :offer_content_id, ce.parent_content_element_id, ce.id) cid, GROUP_CONCAT(b.id) bid
       FROM
        shop_basket b
        INNER JOIN cms_content_element ce ON ce.id = b.product_id
        WHERE b.fuser_id = :fuser_id AND b.has_removed = 0
        group by cid) bp 
     ON bp.cid = ep.value
WHERE
    ep.property_id = :property_id
GROUP BY ep.element_id
HAVING count(ep.value) = count(bp.cid)
';

        $lookbooks = \Yii::$app->db->createCommand(
            $sql,
            [':fuser_id' => $shopFuserId, ':property_id' => $property->id, ':offer_content_id' => OFFERS_CONTENT_ID]
        )->queryAll();

        return $lookbooks;
    }
}
