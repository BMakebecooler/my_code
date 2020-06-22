<?php

namespace modules\shopandshow\models\shop\stock\forms;

use modules\shopandshow\components\highcharts\SeriesDataHelper;
use modules\shopandshow\models\shop\ShopBasket;
use yii\db\Connection;

class SalesStockPeriod extends Stock
{

    protected $periods = [];

    public function init()
    {
        parent::init();
    }

    /**
     * Получение сумарных данных для конкретного дня или периода
     * @param bool $forPeriod - какие даты брать, одного дня или периода
     * @return array
     */
    public function getTotals($forPeriod = false)
    {
        if ($forPeriod) {
            $dateFrom = $this->getPeriodBegin($this->dateFrom);
            $dateTo = $this->getPeriodEnd($this->dateTo);
        } else {
            $dateFrom = $this->getPeriodBegin($this->date);
            $dateTo = $this->getPeriodEnd($this->date);
        }

        $shopBasketsQuery = ShopBasket::find()
            ->select(new \yii\db\Expression(
                'SUM(shop_basket.price * shop_basket.quantity) as product_price, ' .
                'SUM(shop_basket.quantity) as product_quantity, ' .
                'IF(ps.segment IS NULL, 0, 1) as is_stock'
            ))
            ->innerJoin('shop_order', 'shop_order.id = order_id')
            ->leftJoin('ss_products_segments AS ps', 'ps.product_id = shop_basket.main_product_id')
            //->where(['BETWEEN', 'shop_order.created_at', $this->getPeriodBegin($this->date), $this->getPeriodEnd($this->date)])
            ->where(['BETWEEN', 'shop_order.created_at', $dateFrom, $dateTo])
            ->andWhere(['shop_basket.has_removed' => ShopBasket::HAS_REMOVED_FALSE])
            ->andWhere(['NOT', ['shop_basket.order_id' => null]])
            ->groupBy('is_stock')
            ->indexBy('is_stock')
            ->asArray()
            ->all();

        return [
            'total' => ($shopBasketsQuery[0]['product_price'] ?? 0) + ($shopBasketsQuery[1]['product_price'] ?? 0),
            'total_quantity' => ($shopBasketsQuery[0]['product_quantity'] ?? 0) + ($shopBasketsQuery[1]['product_quantity'] ?? 0),
            'stock' => ($shopBasketsQuery[1]['product_price'] ?? 0),
            'stock_quantity' => ($shopBasketsQuery[1]['product_quantity'] ?? 0),
        ];
    }

    /**
     * преобразует массив getBasketStockSalesData в понятный для highCharts
     * @param array $stockSales
     * @return array
     */
    public function prepareForHighCharts(array $stockSales)
    {
        $result = [];
        foreach ($stockSales as $stockSale => $saleSegments) {
            $result[] = ['x' => $stockSale, 'y' => array_sum(array_column($saleSegments, 'price'))];
        }

        return $result;
    }

    /**
     * Получение и форматирование данных для вывода статистики по дням
     * @return array
     */
    public function getBasketStockSalesByPeriod()
    {
        $result = [];

        if ($stockSales = $this->getBasketStockSalesDataByPeriod()) {

            foreach ($stockSales as $sales) {

                @$result[$sales['file_name']][self::SEGMENT_TOTAL]['price'] += $sales['product_price'];
                @$result[$sales['file_name']][self::SEGMENT_TOTAL]['quantity'] += $sales['product_quantity'];

                //Для сегментов стока считаем итого
                if (in_array($sales['segment'], self::$stockTypes)) {
                    @$result[$sales['file_name']][$sales['order_date']][self::SEGMENT_TOTAL_STOCK]['price'] += $sales['product_price'];
                    @$result[$sales['file_name']][$sales['order_date']][self::SEGMENT_TOTAL_STOCK]['quantity'] += $sales['product_quantity'];
                }

                @$result[$sales['file_name']][$sales['order_date']][$sales['segment']]['price'] += $sales['product_price'];
                @$result[$sales['file_name']][$sales['order_date']][$sales['segment']]['quantity'] += $sales['product_quantity'];
            }


            //Все разложено как надо, осталось посчитать доли
            foreach ($result as $periodName => $periods) {
                foreach ($periods as $day => $saleDateData) {
                    foreach ($saleDateData as $saleSegmentId => $saleDateDatum) {
                        //Считать доли имеет смысл только для сегментов стока, общего стока и не стока
                        if (
                            in_array($saleSegmentId, self::$stockTypes)
                            || $saleSegmentId == self::SEGMENT_TOTAL_STOCK
                            || $saleSegmentId == self::SEGMENT_NOT_STOCK
                        ) {
                            $result[$periodName][$day][$saleSegmentId]['stock_part_by_price'] = $result[$periodName][self::SEGMENT_TOTAL]['price'] ?
                                round($result[$periodName][$day][$saleSegmentId]['price'] / $result[$periodName][self::SEGMENT_TOTAL]['price'] * 100, 2) : 0;

                            $result[$periodName][$day][$saleSegmentId]['stock_part_by_quantity'] = $result[$periodName][self::SEGMENT_TOTAL]['quantity'] ?
                                round($result[$periodName][$day][$saleSegmentId]['quantity'] / $result[$periodName][self::SEGMENT_TOTAL]['quantity'] * 100, 2) : 0;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Выборка статистических данных по товарам стока в разрезе дней
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getBasketStockSalesDataByPeriod()
    {

        $this->getPeriods();

        $sql = <<<SQL
SELECT 
    SUM(shop_basket.price * shop_basket.quantity) AS product_price, 
    files.name AS file_name,
    (DATE_FORMAT(FROM_UNIXTIME(shop_order.created_at), '%d.%m.%y')) as order_date,
    SUM(shop_basket.quantity) AS product_quantity, 
    IF(ISNULL(ps.segment), 'NOT_STOCK', ps.segment) AS segment, 
    ps.begin_datetime
FROM shop_order 

INNER JOIN shop_basket ON shop_order.id = shop_basket.order_id    
INNER JOIN ss_products_segments AS ps ON shop_basket.main_product_id = ps.product_id AND ps.begin_datetime >= :begin_datetime
  AND ps.end_datetime <= :end_datetime
INNER JOIN ss_segments_files files ON files.id = ps.file_id 
		
WHERE (ps.file_id IS NOT NULL AND shop_order.created_at >= :begin_datetime AND shop_order.created_at <= :end_datetime) 
  AND (shop_basket.has_removed=0) AND (NOT (shop_basket.order_id IS NULL))
GROUP BY order_date, ps.file_id, segment
SQL;

        $resultSql = [];

        foreach ($this->periods as $period) {

            $periodSql = $sql;

            $periodSql = str_replace(':begin_datetime', $period['begin_datetime'], $periodSql);
            $periodSql = str_replace(':end_datetime', $period['end_datetime'], $periodSql);

            $resultSql[] = $periodSql;
        }

        if ($resultSql) {

            $resultSql = implode(' UNION ALL ', $resultSql);

            $resultSql = <<<SQL
SELECT * 
FROM ({$resultSql}) AS t
ORDER BY order_date ASC
SQL;

            return \Yii::$app->db->createCommand($resultSql)->queryAll();

            return \Yii::$app->db->cache(function (Connection $db) use ($resultSql) {
            }, MIN_5);
        }

        return [];
    }

    /**
     * преобразует массив getBasketStockSalesDataByPeriod в понятный для highCharts
     * @param array $periodsData
     * @return array
     */
    public function prepareHighChartsSeriesByPeriod(array $periodsData, $param = 'price')
    {
        $series = [];

        $colors = [
            '#468499',
            '#ff6666',
            '#c39797',
            '#666666',
            '#00ced1',
            '#ffdab9',
            '#ff00ff',
            '#008000',
            '#660066',
            '#088da5',
            '#f5f5f5',
            '#b0e0e6',
            '#008080',
            '#ffc0cb',
            '#ffe4e1',
            '#ff0000',
            '#ffd700',
            '#40e0d0',
            '#ff7373',
            '#e6e6fa',
            '#0000ff',
            '#ffa500',
            '#f0f8ff',
            '#7fffd4',
            '#c6e2ff',
            '#cccccc',
            '#fa8072',
            '#faebd7',
            '#800080',
            '#ffb6c1',
            '#00ff00',
            '#800000',
            '#333333',
            '#003366',
            '#20b2aa',
            '#c0c0c0',
            '#ffc3a0',
            '#f08080',
            '#f6546a',
            '#66cdaa',
            '#c0d6e4',
            '#8b0000',
            '#ff7f50',
            '#0e2f44',
            '#afeeee',
            '#990000',
            '#808080',
            '#dddddd',
            '#daa520',
            '#b4eeb4',
            '#cbbeb5',
            '#00ff7f',
            '#8a2be2',
            '#3399ff',
            '#ff4040',
            '#81d8d0',
            '#66cccc',
            '#b6fcd5',
            '#a0db8e',
            '#cc0000',
            '#794044',
            '#000080',
            '#3b5998',
            '#0099cc',
            '#6897bb',
            '#999999',
            '#191970',
            '#ff1493',
            '#31698a',
            '#fef65b',
            '#ff4444',
            '#6dc066',
            '#191919',
        ];

        $segmentsColor = [
            'A' => '#f6546a',
            'B' => '#b4eeb4',
            'C' => '#0099cc',
            'D' => '#666666',
            'TOTAL' => '#6dc066',
        ];

        $colorIndex = $segmentColorIndex = 0;

        foreach ($periodsData as $periodName => $sales) {

            $color = isset($colors[$colorIndex]) ? $colors[$colorIndex] : $colors[0];

            foreach ($sales as $day => $saleSegments) {

                if ($day == self::SEGMENT_TOTAL) {
                    $series[sprintf('Всего %s', $periodName)][] = [
                        'color' => $color,
                        'name' => 'Всего',
                        'y' => $saleSegments[$param] ?? 0
                    ];

                    continue;
                }

                foreach ($saleSegments as $saleSegmentId => $saleSegment) {

                    if ($saleSegmentId == self::SEGMENT_TOTAL_STOCK) {
                        $color = isset($colors[$colorIndex]) ? $colors[$colorIndex] : $colors[0];
                    } else {
                        $color = isset($segmentsColor[$saleSegmentId]) ? $segmentsColor[$saleSegmentId] : $color;
                    }

                    $series[$saleSegmentId][] = [
                        'color' => $color,
                        'name' => $day,
                        'y' => $saleSegment[$param] ?? 0
                    ];

                }
            }

            $colorIndex++;
        }

        return $series;
    }

    /**
     * Преобразование предвариательно подготовленных данных в формат массива пригодного для посмотроения графика
     * @param array $sales
     * @param $title
     * @param array $config
     * @return array
     */
    public function getHighchartsDataByPeriod(array $sales, $title, $config = [])
    {
        $series = [];
        foreach ($sales as $segmentId => $sale) {

            //Что бы не перегружать изначально графики информацией, покажем только самое главное - суммарные данные по стоку
            $serieVisible = $segmentId == self::SEGMENT_TOTAL_STOCK;

            $series[] = [
                'name' => self::getSegmentLabel($segmentId),
                'data' => (new SeriesDataHelper($sale, ['name:string', 'y:float', 'color:string']))->process(),
                'visible' => $serieVisible,
            ];
        }

        return $this->getHighchartsTemplateByPeriod($title ?: 'НЕ УКАЗАНО', $series, $config);
    }

    /**
     * Получение полного конфига с набором данных для построения графика
     * @param $title
     * @param array $series
     * @param array $config
     * @return array
     */
    public function getHighchartsTemplateByPeriod($title, array $series, $config = [])
    {

        $defaultConfig = [
            'options' => [
                'chart' => [
                    'type' => 'column',
                ],
                'title' => ['text' => $title],

                'xAxis' => [
                    'type' => 'category',
                ],
                'yAxis' => [
                    [
                        'title' => ['text' => 'Сумма, руб.'],
                    ]
                ],
                'legend' => [
                    'enabled' => true,
                ],
                'plotOptions' => [
                    'column' => [
                        'colorByPoint' => true
                    ]
                ],

                'series' => $series
            ]
        ];

        return array_replace_recursive($defaultConfig, $config);
    }


    public function getPeriods()
    {
        $sql = <<<SQL
        SELECT *
        FROM ss_segments_files AS s
        WHERE s.begin_datetime >= :begin_datetime AND s.end_datetime <= :end_datetime;
SQL;

        $this->periods = \Yii::$app->db->createCommand($sql, [
            ':begin_datetime' => strtotime($this->dateFrom),
            ':end_datetime' => strtotime($this->dateTo),
        ])->queryAll();
    }
}