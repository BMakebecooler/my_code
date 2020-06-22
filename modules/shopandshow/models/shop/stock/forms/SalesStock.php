<?php

namespace modules\shopandshow\models\shop\stock\forms;

use miloschuman\highcharts\SeriesDataHelper;
use modules\shopandshow\models\shop\ShopBasket;

class SalesStock extends Stock
{

    /**
     * @param int $treeId
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function getBasketStockSales($treeId = null)
    {
        $result = [];
        $beginDate = $this->getPeriodBegin($this->date);
        for ($i = 0; $i < 24; $i++) {
            $result[$beginDate + $i * HOUR_1] = [];
        }
        $stockSales = $this->getBasketStockSalesData($treeId);

        foreach ($stockSales as $sales) {
            @$result[$sales['order_date']][$sales['segment']]['price'] += $sales['product_price'];
            @$result[$sales['order_date']][$sales['segment']]['quantity'] += $sales['product_quantity'];
        }

        return $result;
    }

    /**
     * @param int $treeId
     * @return array|\yii\db\ActiveRecord[]
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function getBasketStockSalesData($treeId = null)
    {
        $shopBasketsQuery = ShopBasket::find()
            ->select(new \yii\db\Expression(
                'SUM(shop_basket.price * shop_basket.quantity) as product_price, ' .
                'UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(shop_order.created_at), "%Y-%m-%d %H:00:00")) as order_date, ' .
                'SUM(shop_basket.quantity) as product_quantity, ' .
                'ps.segment'
            ))
            ->innerJoin('shop_order', 'shop_order.id = order_id')
            ->innerJoin('ss_products_segments AS ps', 'ps.product_id = shop_basket.main_product_id')
            ->where(['BETWEEN', 'shop_order.created_at', $this->getPeriodBegin($this->date), $this->getPeriodEnd($this->date)])
            ->andWhere(['shop_basket.has_removed' => ShopBasket::HAS_REMOVED_FALSE])
            ->andWhere(['NOT', ['shop_basket.order_id' => null]]);

        if ($treeId) {
            $tree = \common\lists\TreeList::getTreeById($treeId);
            $treeQuery = $tree->getDescendants()->select('id');
            // исключаем сад
            if ($tree->code == 'dom') {
                $treeQuery->andWhere('dir NOT LIKE \'catalog/dom/tovary-dlya-dachi%\'');
            } // исключаем здоровье
            elseif ($tree->code == 'krasota-i-zdorove') {
                $treeQuery->andWhere('dir NOT LIKE \'catalog/krasota-i-zdorove/kosmetika%\'');
            }
            $trees = array_merge([$treeId], $treeQuery->asArray()->column());

            $shopBasketsQuery
                ->innerJoin('cms_content_element cce', 'cce.id = main_product_id')
                ->andWhere(['cce.tree_id' => $trees]);
        }

        return $shopBasketsQuery
            ->groupBy('order_date, segment')
            ->orderBy('order_date')
            ->asArray()
            ->all();
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
     * выдает массив для построения графика
     * @param array $stockSales
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getHighchartsData(array $stockSales)
    {
        $series = [
            [
                'name' => 'Продажи, руб.',
                'data' => (new SeriesDataHelper($stockSales, ['x:timestamp', 'y:int']))->process(),
            ]
        ];
        return $this->getHighchartsTemplate('Продажи стоков', $series);
    }

    /**
     * Шаблон под highcharts графики
     * @param string $title
     * @param array $series
     * @param array $config
     * @return array
     */
    public function getHighchartsTemplate($title, array $series, $config = [])
    {
        $defaultConfig = [
            'options' => [
                'chart' => [
                    'zoomType' => 'x',
                    'type' => 'line',
                    'panning' => true,
                    'panKey' => 'shift'
                ],
                'title' => ['text' => $title],
                'xAxis' => [
                    'type' => 'datetime',
                    'min' => $this->getPeriodBegin($this->date) * 1000,
                    'max' => $this->getPeriodEnd($this->date) * 1000,
                    'tickInterval' => HOUR_1 * 1000 // 1 hour
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
                    'line' => [
                        'dataLabels' => [
                            'enabled' => true,
                        ],
                        'enableMouseTracking' => true
                    ]
                ],
                'series' => $series
            ]
        ];

        return array_replace_recursive($defaultConfig, $config);
    }

    /**
     * Получение и форматирование данных для вывода статистики по дням
     * @return array
     */
    public function getBasketStockSalesByDay()
    {
        $result = [];

        $stockSales = $this->getBasketStockSalesDataByDay();

        if ($stockSales) {
            $beginDate = $this->getPeriodBegin($this->dateFrom);
            $endDate = $this->getPeriodEnd($this->dateTo);

            $curDate = $beginDate;
            while ($curDate <= $endDate) {
                $result[$curDate] = [];
                $curDate = $curDate + DAYS_1;
            }

            foreach ($stockSales as $sales) {
                @$result[$sales['order_date']][self::SEGMENT_TOTAL_STOCK]['price'] += $sales['product_price'];
                @$result[$sales['order_date']][self::SEGMENT_TOTAL_STOCK]['quantity'] += $sales['product_quantity'];

                //Для сегментов стока считаем итого
                if (in_array($sales['segment'], self::$stockTypes)) {
                    @$result[$sales['order_date']][self::SEGMENT_TOTAL_STOCK]['price'] += $sales['product_price'];
                    @$result[$sales['order_date']][self::SEGMENT_TOTAL_STOCK]['quantity'] += $sales['product_quantity'];
                }

                @$result[$sales['order_date']][$sales['segment']]['price'] += $sales['product_price'];
                @$result[$sales['order_date']][$sales['segment']]['quantity'] += $sales['product_quantity'];
            }

            //Все разложено как надо, осталось посчитать доли
            foreach ($result as $saleDate => $saleDateData) {
                foreach ($saleDateData as $saleSegmentId => $saleDateDatum) {
                    //Считать доли имеет смысл только для сегментов стока, общего стока и не стока
                    if (
                        in_array($saleSegmentId, self::$stockTypes)
                        || $saleSegmentId == self::SEGMENT_TOTAL_STOCK
                        || $saleSegmentId == self::SEGMENT_NOT_STOCK
                    ) {
                        $result[$saleDate][$saleSegmentId]['stock_part_by_price'] = $result[$saleDate][self::SEGMENT_TOTAL_STOCK]['price'] ?
                            round($result[$saleDate][$saleSegmentId]['price'] / $result[$saleDate][self::SEGMENT_TOTAL_STOCK]['price'] * 100, 2) : 0;

                        $result[$saleDate][$saleSegmentId]['stock_part_by_quantity'] = $result[$saleDate][self::SEGMENT_TOTAL_STOCK]['quantity'] ?
                            round($result[$saleDate][$saleSegmentId]['quantity'] / $result[$saleDate][self::SEGMENT_TOTAL_STOCK]['quantity'] * 100, 2) : 0;

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
    public function getBasketStockSalesDataByDay()
    {
        $shopBasketsQuery = ShopBasket::find()
            ->select(new \yii\db\Expression(
                "SUM(shop_basket.price * shop_basket.quantity) as product_price, " .
                "UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(shop_order.created_at), '%Y-%m-%d 00:00:00')) as order_date, " .
                "SUM(shop_basket.quantity) as product_quantity, " .
                "IF(ISNULL(ps.segment), '" . self::SEGMENT_NOT_STOCK . "', ps.segment) AS segment"
            ))
            ->innerJoin('shop_order', 'shop_order.id = order_id')
            ->leftJoin('ss_products_segments AS ps', 'ps.product_id = shop_basket.main_product_id')
            ->where(['BETWEEN', 'shop_order.created_at', $this->getPeriodBegin($this->dateFrom), $this->getPeriodEnd($this->dateTo)])
            ->andWhere(['shop_basket.has_removed' => ShopBasket::HAS_REMOVED_FALSE])
            ->andWhere(['NOT', ['shop_basket.order_id' => null]]);

        return $shopBasketsQuery
            ->groupBy('order_date, segment')
            ->orderBy('order_date')
            ->asArray()
            ->all();
    }

    /**
     * преобразует массив getBasketStockSalesDataByDay в понятный для highCharts
     * @param array $sales
     * @return array
     */
    public function prepareHighChartsSeriesByDay(array $sales, $param = 'price')
    {
        $series = [];
        foreach ($sales as $saleDate => $saleSegments) {
            foreach ($saleSegments as $saleSegmentId => $saleSegment) {
                $series[$saleSegmentId][] = ['x' => $saleDate, 'y' => $saleSegment[$param] ?? 0];
            }

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
    public function getHighchartsDataByDay(array $sales, $title, $config = [])
    {
        $series = [];
        foreach ($sales as $segmentId => $sale) {

            //Что бы не перегружать изначально графики информацией, покажем только самое главное - суммарные данные по стоку
            $serieVisible = $segmentId == self::SEGMENT_TOTAL_STOCK;

            $series[] = [
                'name' => self::getSegmentLabel($segmentId),
                'data' => (new SeriesDataHelper($sale, ['x:timestamp', 'y:float']))->process(),
                'visible' => $serieVisible
            ];
        }

        return $this->getHighchartsTemplateByDay($title ?: 'НЕ УКАЗАНО', $series, $config);
    }

    /**
     * Получение полного конфига с набором данных для построения графика
     * @param $title
     * @param array $series
     * @param array $config
     * @return array
     */
    public function getHighchartsTemplateByDay($title, array $series, $config = [])
    {
        $defaultConfig = [
            'options' => [
                'chart' => [
                    'zoomType' => 'x',
                    'type' => 'line',
                    'panning' => true,
                    'panKey' => 'shift'
                ],
                'title' => ['text' => $title],
                'xAxis' => [
                    'type' => 'datetime',
                    'min' => $this->getPeriodBegin($this->dateFrom) * 1000,
                    'max' => $this->getPeriodEnd($this->dateTo) * 1000,
                    'tickInterval' => DAYS_1 * 1000 // 1 hour
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
                    'line' => [
                        'dataLabels' => [
                            'enabled' => true,
                        ],
                        'enableMouseTracking' => true
                    ]
                ],
                'series' => $series
            ]
        ];

        return array_replace_recursive($defaultConfig, $config);
    }
}