<?php
namespace modules\shopandshow\models\monitoringday;


use common\helpers\ArrayHelper;
use common\models\cmsContent\CmsContentElement;

class PlanHelper
{
    // цена закупки PURCHASE_PRICE
    const PURCHASE_PROPERTY_ID = 14;

    /**
     * sql запрос для основной таблицы
     * @param string $andCondition
     * @return string
     */
    public static function getPlanSql($andCondition = '')
    {
        $priceDeliverySite = Plan::PRICE_DELIVERY_SITE;
        $query = <<<SQL
SELECT 
  ph.hour,   
  (
    SELECT mp.section_name 
    FROM ss_mediaplan_air_blocks mp 
    WHERE mp.begin_datetime >= (:begin_datetime + 3600 * ph.hour) AND mp.begin_datetime < (:begin_datetime + 3600 * (ph.hour+1))
    LIMIT 1
  ) category,
  round(pd.sum_plan * ph.percent / 100, 2) sum_plan, 
  sum(so.price + if(so.source = 'site', {$priceDeliverySite}, 0)) sum_fact,
  count(so.id) amount
FROM ss_monitoring_plan_day pd
LEFT JOIN ss_monitoring_plan_hour ph ON ph.plan_id = pd.id
LEFT JOIN shop_order so ON so.created_at >= (:begin_datetime + 3600 * ph.hour) AND so.created_at < (:begin_datetime + 3600 * (ph.hour+1))
WHERE pd.date = :date AND pd.type_plan = :type_plan
{$andCondition}
GROUP BY ph.hour
ORDER BY ph.hour
SQL;

        return $query;
    }

    /**
     * sql запрос для дополнительной таблицы с разбивкой по категориям
     * @return string
     */
    public static function getCategoryPlanSql()
    {
        $purchasePropertyId = self::PURCHASE_PROPERTY_ID;
        $query = <<<SQL
SELECT 
  ph.hour,  
  (
    SELECT mp.section_name 
    FROM ss_mediaplan_air_blocks mp 
    WHERE mp.begin_datetime >= (:begin_datetime + 3600 * ph.hour) AND mp.begin_datetime < (:begin_datetime + 3600 * (ph.hour+1))
    LIMIT 1
  ) category,
  sum(sb.price * sb.quantity) sum_category,
  sum((sb.price - if(margin.price is null or margin.price = '' or margin.price = 0, sb.price, margin.price)) * sb.quantity) sum_margin_category,
  SUBSTRING(
		SUBSTRING_INDEX(CONCAT(ct.pids,'/',ct.id) , '/' , 3),
		CHAR_LENGTH(
			SUBSTRING_INDEX(CONCAT(ct.pids,'/',ct.id) , '/' , 2)
		) + 2
	) parent_tree_id
FROM ss_monitoring_plan_day pd
LEFT JOIN ss_monitoring_plan_hour ph ON ph.plan_id = pd.id
LEFT JOIN shop_order so ON so.created_at >= (:begin_datetime + 3600 * ph.hour) AND so.created_at < (:begin_datetime + 3600 * (ph.hour+1))
LEFT JOIN shop_basket sb ON sb.order_id = so.id
LEFT JOIN cms_content_element cce ON cce.id = sb.main_product_id
LEFT JOIN cms_tree ct ON ct.id = cce.tree_id
LEFT JOIN shop_product_price margin ON margin.product_id = sb.main_product_id AND margin.type_price_id = {$purchasePropertyId}
WHERE pd.date = :date AND pd.type_plan = :type_plan
GROUP BY ph.hour, parent_tree_id
ORDER BY ph.hour, parent_tree_id
SQL;

        return $query;
    }

    /**
     * Считает сумму эфира по данным битрикс
     * @param $beginDateTime string
     * @param $endDateTime string
     * @return array
     */
    public static function getTotalSum($beginDateTime, $endDateTime)
    {
        return self::getEfirTotalSum($beginDateTime, $endDateTime);
    }

    /**
     * Считает сумму эфира по данным битрикс
     * @param $beginDateTime string Y-m-d H:i:s
     * @param $endDateTime string Y-m-d H:i:s
     * @param $cts CmsContentElement
     * @return double
     */
    public static function getCtsTotalSum($beginDateTime, $endDateTime, $cts)
    {
        if (!\Yii::$app->has('front_db')) {
            return 0;
        }

        $bitrixCtsIds = [$cts->bitrix_id];
        /** @var CmsContentElement $childrenContentElement */
        foreach ($cts->childrenContentElements as $childrenContentElement) {
            $bitrixCtsIds[] = $childrenContentElement->bitrix_id;
        }
        $bitrixCtsIds = join(',', ArrayHelper::arrayToInt($bitrixCtsIds));

        $query = <<<SQL
SELECT SUM(b.price * b.quantity) price
FROM b_sale_order o
INNER JOIN b_sale_basket b 
        ON b.order_id = o.id
INNER JOIN b_sale_order_props_value as o_source 
        ON o.id = o_source.order_id and o_source.ORDER_PROPS_ID = 12
INNER JOIN b_sale_order_props_value as o_phone 
        ON o.id = o_phone.order_id  and o_phone.ORDER_PROPS_ID  = 16
WHERE o.DATE_INSERT BETWEEN :begin_datetime AND :end_datetime 
  AND b.product_id IN ($bitrixCtsIds)
  AND o_source.value IN ('FRONT', 'NATIVE')
  AND o_phone.value NOT IN (
    -- '88003016040', -- TV Орион # 8 (800) 301-60-40
    '88003016010', -- Сайт # 8 (800) 301-60-10
    '88007752250', -- Новый сайт # 8 (800) 775-22-50
    '88003016020', -- Каталог # 8 (800) 301-60-20
    '88003016030', -- Каталог Осень 2015-10 # 8 (800) 301-60-30
    '84957772309', -- Телемаркетинг # 8 (495) 777-23-09
    '88003016080'  -- Каталог Внешняя база # 8 (800) 301-60-80
  )
SQL;

        $result = \Yii::$app->front_db->createCommand($query, [':begin_datetime' => $beginDateTime, ':end_datetime' => $endDateTime])->queryScalar();

        return $result;
    }

    /**
     * Считает сумму эфира по данным битрикс
     * @param $beginDateTime string Y-m-d H:i:s
     * @param $endDateTime string Y-m-d H:i:s
     * @return array
     */
    public static function getEfirTotalSum($beginDateTime, $endDateTime)
    {
        if (!\Yii::$app->has('front_db')) {
            return [];
        }

        $query = <<<SQL
SELECT SUM(o.price) order_price, DATE_FORMAT(o.DATE_INSERT, "%Y-%m-%d %H:00:00") order_date
FROM b_sale_order o
INNER JOIN b_sale_order_props_value as o_source 
        ON o.id = o_source.order_id and o_source.ORDER_PROPS_ID = 12
INNER JOIN b_sale_order_props_value as o_phone 
        ON o.id = o_phone.order_id  and o_phone.ORDER_PROPS_ID  = 16
WHERE o.DATE_INSERT BETWEEN :begin_datetime AND :end_datetime 
  AND o_source.value IN ('FRONT', 'NATIVE')
  AND o_phone.value NOT IN (
    -- '88003016040', -- TV Орион # 8 (800) 301-60-40
    '88003016010', -- Сайт # 8 (800) 301-60-10
    '88007752250', -- Новый сайт # 8 (800) 775-22-50
    '88003016020', -- Каталог # 8 (800) 301-60-20
    '88003016030', -- Каталог Осень 2015-10 # 8 (800) 301-60-30
    '84957772309', -- Телемаркетинг # 8 (495) 777-23-09
    '88003016080'  -- Каталог Внешняя база # 8 (800) 301-60-80
  )
GROUP BY order_date
SQL;

        $result = \Yii::$app->front_db->createCommand($query, [':begin_datetime' => $beginDateTime, ':end_datetime' => $endDateTime])->queryAll();
        return \common\helpers\ArrayHelper::map($result, 'order_date', 'order_price');
    }
}