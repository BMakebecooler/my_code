<?php
namespace modules\shopandshow\models\statistic;


class StatisticsQuery
{

    /**
     * Вчерашние топы продаж
     * @return string
     */
    public static function getYesterdayTopQuery()
    {
        $query = <<<SQL
SELECT lot_id, sp.quantity, cce.id AS product_id, count(basket.id) AS count_sale
FROM (
  SELECT lot_id FROM ss_mediaplan_air_day_product_time 
  WHERE begin_datetime >= :begin_datetime  AND end_datetime <= :end_datetime 
  GROUP BY lot_id
) AS mp
INNER JOIN cms_content_element AS cce ON cce.id = mp.lot_id
INNER JOIN shop_product AS sp ON sp.id = cce.id
INNER JOIN shop_basket AS basket ON basket.main_product_id = cce.id AND basket.created_at >= :begin_datetime AND basket.created_at <= :end_datetime
WHERE basket.order_id IS NOT NULL
GROUP BY lot_id
ORDER BY count_sale DESC
LIMIT 50
SQL;

        return $query;
    }

    /**
     * Брошенные корзины
     * @return string
     */
    public static function getAbandonedBasketQuery()
    {
        $query = <<<SQL
SELECT logs.*
FROM ss_preorders_logs AS logs
ORDER BY logs.created_at DESC
SQL;

        return $query;
    }

    /**
     * Брошенные корзины - отчет по выгрузкам
     * @return string
     */
    public static function getAbandonedBasketReportQuery()
    {
        $query = <<<SQL
SELECT created_at AS date, count(*) AS count
FROM `ss_preorders_logs`
WHERE created_at >= LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 1 MONTH
GROUP BY DAY(created_at), HOUR(created_at) , MINUTE(created_at)
ORDER BY created_at DESC
SQL;

        return $query;
    }


    /**
     * Короче, в МП полная каша:
     * в таблице ss_mediaplan_air_blocks время указано в GMT
     * в таблице ss_mediaplan_air_day_product_time время указано в GMT для товаров, которые в эфире не показывались (кроссы), и в MSK - которые показывались
     * @return string
     */
    public static function getRealtimeEfirQuery()
    {
        $query = <<<SQL
SELECT s.*,
IFNULL((count_add_basket_day / count_all_viewed), 0) AS convercy_add_basket_day,
IFNULL((count_add_order_day / count_all_viewed), 0) AS convercy_add_order_day

FROM (
    
    SELECT 
        mp.id,
        mp.lot_id, 
        ab.begin_datetime AS air_begin_datetime,
        ab.end_datetime AS air_end_datetime,
        mp.begin_datetime,
        mp.end_datetime,
        cce.id AS product_id, 
        sp.quantity,
        (
            SELECT COUNT(sv.id)
              FROM shop_viewed_product AS sv
              WHERE sv.created_at >= :begin_datetime AND sv.created_at <= :end_datetime
                AND shop_product_id = cce.id
        ) AS count_all_viewed, 
        (
            SELECT COUNT(DISTINCT basket.id) AS count
              FROM shop_basket AS basket
              WHERE basket.created_at >= :begin_datetime AND basket.created_at <= :end_datetime
                AND basket.main_product_id = cce.id
              
        ) AS count_add_basket_day,
        (
            SELECT COUNT(DISTINCT basket.id) AS count
              FROM shop_basket AS basket
              WHERE basket.created_at >= :begin_datetime AND basket.created_at <= :end_datetime
                AND basket.main_product_id = cce.id
                AND basket.order_id IS NOT NULL
              
        ) AS count_add_order_day,
        (
            SELECT SUM(basket.price * basket.quantity) AS sum
              FROM shop_basket AS basket
              WHERE basket.created_at >= :begin_datetime AND basket.created_at <= :end_datetime
                AND basket.main_product_id = cce.id
                AND basket.order_id IS NOT NULL
        ) AS sum_add_order_day
    FROM ss_mediaplan_air_day_product_time AS mp
    INNER JOIN ss_mediaplan_air_blocks AS ab ON ab.block_id = mp.block_id
    INNER JOIN cms_content_element AS cce ON cce.id = mp.lot_id
    INNER JOIN shop_product AS sp ON sp.id = cce.id    
    WHERE mp.begin_datetime >= :begin_datetime AND mp.end_datetime <= :end_datetime 
) AS s
ORDER BY s.air_begin_datetime ASC, s.id ASC
SQL;
        return $query;
    }

    /**
     * Данные для графика по кол-ву просмотров
     * @param int $interval - временной интервал, по которому группировать данные
     *
     * @return string
     */
    public static function getCountViewedQuery($interval = 600)
    {
        $query = <<<SQL
SELECT x, count(*) AS y 
FROM (
    SELECT FLOOR(created_at / {$interval}) * {$interval} AS x 
    FROM shop_viewed_product sv 
    WHERE sv.shop_product_id = :product_id
      AND sv.created_at >= :begin_datetime AND sv.created_at <= :end_datetime
) a 
GROUP BY x 
ORDER BY x
SQL;

        return $query;
    }

    /**
     * Данные для графика по кол-ву накопленных просмотров
     * @param int $interval - временной интервал, по которому группировать данные
     *
     * @return string
     */
    public static function getCountViewedSummaryQuery($interval = 600)
    {
        \Yii::$app->db->createCommand('SET @y := 0;')->execute();

        $query = <<<SQL
SELECT x, (@y := y + @y) AS y 
FROM (
    SELECT x, count(*) AS y 
    FROM (
        SELECT FLOOR(created_at / {$interval}) * {$interval} AS x 
        FROM shop_viewed_product sv 
        WHERE sv.shop_product_id = :product_id
          AND sv.created_at >= :begin_datetime AND sv.created_at <= :end_datetime
    ) a 
    GROUP BY x 
) b
ORDER BY x
SQL;

        return $query;
    }

    /**
     * Данные для графика по кол-ву просмотров
     * @param int $interval - временной интервал, по которому группировать данные
     *
     * @return string
     */
    public static function getCountBasketQuery($interval = 600)
    {
        $query = <<<SQL
SELECT x, count(*) AS y 
FROM (
    SELECT FLOOR(created_at / {$interval}) * {$interval} AS x 
    FROM shop_basket sb 
    WHERE sb.main_product_id = :product_id
      AND sb.created_at >= :begin_datetime AND sb.created_at <= :end_datetime
) a 
GROUP BY x 
ORDER BY x
SQL;

        return $query;
    }

    /**
     * Данные для графика по кол-ву накопленных просмотров
     * @param int $interval - временной интервал, по которому группировать данные
     *
     * @return string
     */
    public static function getCountBasketSummaryQuery($interval = 600)
    {
        \Yii::$app->db->createCommand('SET @y := 0;')->execute();

        $query = <<<SQL
SELECT x, (@y := y + @y) AS y 
FROM (
    SELECT x, count(*) AS y 
    FROM (
        SELECT FLOOR(created_at / {$interval}) * {$interval} AS x 
        FROM shop_basket sb 
        WHERE sb.main_product_id = :product_id
          AND sb.created_at >= :begin_datetime AND sb.created_at <= :end_datetime
    ) a 
    GROUP BY x 
) b
ORDER BY x
SQL;

        return $query;
    }

    /**
     * Данные для графика по кол-ву просмотров
     * @param int $interval - временной интервал, по которому группировать данные
     *
     * @return string
     */
    public static function getBasketConvercyQuery($interval = 600)
    {
        $query = <<<SQL
SELECT viewed.x AS x, IFNULL(basket.y / viewed.y, 0) * 100 AS y 
FROM (
    SELECT x, count(*) AS y 
    FROM (
        SELECT FLOOR(created_at / {$interval}) * {$interval} AS x 
        FROM shop_viewed_product sv 
        WHERE sv.shop_product_id = :product_id
          AND sv.created_at >= :begin_datetime AND sv.created_at <= :end_datetime
    ) a 
    GROUP BY x 
) viewed
LEFT JOIN (
    SELECT x, count(*) AS y 
    FROM (
        SELECT FLOOR(created_at / {$interval}) * {$interval} AS x 
        FROM shop_basket sb 
        WHERE sb.main_product_id = :product_id
          AND sb.created_at >= :begin_datetime AND sb.created_at <= :end_datetime
    ) a 
    GROUP BY x 
) basket ON viewed.x = basket.x
ORDER BY viewed.x
SQL;

        return $query;
    }


    /**
     * Данные для графика по кол-ву заказов
     * @param int $interval - временной интервал, по которому группировать данные
     *
     * @return string
     */
    public static function getCountOrderQuery($interval = 600)
    {
        $query = <<<SQL
SELECT x, count(*) AS y 
FROM (
    SELECT FLOOR(created_at / {$interval}) * {$interval} AS x 
    FROM shop_basket sb 
    WHERE sb.main_product_id = :product_id
      AND sb.created_at >= :begin_datetime AND sb.created_at <= :end_datetime
      AND sb.order_id IS NOT NULL
) a 
GROUP BY x 
ORDER BY x
SQL;

        return $query;
    }

    /**
     * Данные для графика по кол-ву накопленных заказов
     * @param int $interval - временной интервал, по которому группировать данные
     *
     * @return string
     */
    public static function getCountOrderSummaryQuery($interval = 600)
    {
        \Yii::$app->db->createCommand('SET @y := 0;')->execute();

        $query = <<<SQL
SELECT x, (@y := y + @y) AS y 
FROM (
    SELECT x, count(*) AS y 
    FROM (
        SELECT FLOOR(created_at / {$interval}) * {$interval} AS x 
        FROM shop_basket sb 
        WHERE sb.main_product_id = :product_id
          AND sb.created_at >= :begin_datetime AND sb.created_at <= :end_datetime
          AND sb.order_id IS NOT NULL
    ) a 
    GROUP BY x 
) b
ORDER BY x
SQL;

        return $query;
    }

    /**
     * Данные для графика конверсии для заказов
     * @param int $interval - временной интервал, по которому группировать данные
     *
     * @return string
     */
    public static function getOrderConvercyQuery($interval = 600)
    {
        $query = <<<SQL
SELECT viewed.x AS x, IFNULL(basket.y / viewed.y, 0) * 100 AS y 
FROM (
    SELECT x, count(*) AS y 
    FROM (
        SELECT FLOOR(created_at / {$interval}) * {$interval} AS x 
        FROM shop_viewed_product sv 
        WHERE sv.shop_product_id = :product_id
          AND sv.created_at >= :begin_datetime AND sv.created_at <= :end_datetime
    ) a 
    GROUP BY x 
) viewed
LEFT JOIN (
    SELECT x, count(*) AS y 
    FROM (
        SELECT FLOOR(created_at / {$interval}) * {$interval} AS x 
        FROM shop_basket sb 
        WHERE sb.main_product_id = :product_id
          AND sb.created_at >= :begin_datetime AND sb.created_at <= :end_datetime
          AND sb.order_id IS NOT NULL
    ) a 
    GROUP BY x 
) basket ON viewed.x = basket.x
ORDER BY viewed.x
SQL;

        return $query;
    }

    public static function getEfirTotalQuery($condition)
    {
        $query = <<<SQL
SELECT cce.id, SUM(bb.price * bb.quantity) sum_efir
FROM cms_content_element cce
INNER JOIN front2.b_iblock_element be ON be.id = cce.bitrix_id
INNER JOIN front2.b_sale_basket bb ON bb.product_id = be.id OR bb.product_id IN
  (
    SELECT IBLOCK_ELEMENT_ID 
    FROM front2.b_iblock_element_property 
    WHERE VALUE = be.id AND IBLOCK_PROPERTY_ID = 58
  )
INNER JOIN front2.b_sale_order bo ON bo.id = bb.order_id
INNER JOIN front2.b_sale_order_props_value o_source 
        ON bo.id = o_source.order_id and o_source.ORDER_PROPS_ID = 12
INNER JOIN front2.b_sale_order_props_value as o_phone 
        ON bo.id = o_phone.order_id  and o_phone.ORDER_PROPS_ID  = 16
WHERE bo.date_insert BETWEEN :begin_datetime AND :end_datetime
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
  AND $condition
GROUP by cce.id
SQL;

        return $query;
    }
}