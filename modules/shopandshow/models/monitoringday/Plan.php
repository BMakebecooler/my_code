<?php

namespace modules\shopandshow\models\monitoringday;

use common\lists\TreeList;
use common\models\api\ga\v4\GoogleAnalytics;
use miloschuman\highcharts\SeriesDataHelper;
use modules\shopandshow\models\mediaplan\AirBlock;
use modules\shopandshow\models\mediaplan\AirDayProductTime;
use modules\shopandshow\models\shares\SsShare;
use modules\shopandshow\models\shop\ShopBasket;
use modules\shopandshow\models\shop\ShopOrder;
use skeeks\cms\components\Cms;
use yii\helpers\Json;
use yii\httpclient\Client;

class Plan extends \yii\base\Model
{
    // средняя соимость доставки заказа
    const PRICE_DELIVERY_SITE = 0;

    public $date;
    public $autoUpdate = 60;
    public $showCts = 0;

    public $dateTimeOffset = 0;

    // поправляющий коэффициент (я не хотел, меня заставили)
    public $factor = 0;

    public function init()
    {
        parent::init();

        if (!$this->date) {
            $this->date = date('Y-m-d');
        }

        $this->factor = floatval(\Yii::$app->shopAndShowSettings->monitoringDayFactor) / 100;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date'], 'required'],
            [['date'], 'string'],
            [['autoUpdate', 'showCts'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'date' => 'Дата',
            'autoUpdate' => 'Автоматически обновлять страницу каждые N секунд (0 - не обновлять)',
            'showCts' => 'Товар ЦТС отдельной строкой',
        ];
    }


    /**
     * Основной провайдер для таблицы отчета
     * @return \yii\data\SqlDataProvider
     */
    public function getDataProvider()
    {
        $dataProvider = new \yii\data\SqlDataProvider([
            'sql' => PlanHelper::getPlanSql(),
            'params' => [
                ':date' => $this->date,
                ':begin_datetime' => $this->getPeriodBegin($this->date),
                ':type_plan' => PlanDay::TYPE_SITE,
            ],
//            'totalCount' => $totalCount,
            'pagination' => false,
            'sort' => false,
        ]);

        $this->adjustDataProdiverByFactor($dataProvider, ['sum_fact']);
        $this->calcAccValues($dataProvider);

        return $dataProvider;
    }

    /**
     * провайдер для таблицы отчета по указанному источнику
     * @param string $source
     * @return \yii\data\SqlDataProvider
     */
    public function getDataProviderBySource($source)
    {
        $dataProvider = new \yii\data\SqlDataProvider([
            'sql' => PlanHelper::getPlanSql(sprintf("AND so.source = '%s'", $source)),
            'params' => [
                ':date' => $this->date,
                ':begin_datetime' => $this->getPeriodBegin($this->date),
                ':type_plan' => PlanDay::TYPE_SITE,
            ],
//            'totalCount' => $totalCount,
            'pagination' => false,
            'sort' => false,
        ]);

        $this->adjustDataProdiverByFactor($dataProvider, ['sum_fact']);

        return $dataProvider;
    }

    /**
     * Дополнительный провайдер данных для таблицы с разбивкой по категориям
     * @return \yii\data\SqlDataProvider
     */
    public function getCategoryDataProvider()
    {
        $dataProvider = new \yii\data\SqlDataProvider([
            'sql' => PlanHelper::getCategoryPlanSql(),
            'params' => [
                ':date' => $this->date,
                ':begin_datetime' => $this->getPeriodBegin($this->date),
                ':type_plan' => PlanDay::TYPE_SITE,
            ],
//            'totalCount' => $totalCount,
            'pagination' => false,
            'sort' => false,
        ]);

        $this->adjustDataProdiverByFactor($dataProvider, ['sum_category', 'sum_margin_category']);
        $this->calcCategoryValues($dataProvider);

        return $dataProvider;
    }

    /**
     * формирует список столбцов с категориями для виджета gridview
     * @param \yii\data\BaseDataProvider $dataProvider
     * @return array
     */
    public function getCategoryColumns(\yii\data\BaseDataProvider $dataProvider)
    {
        $columns = [
            [
                'label' => "Время",
                'value' => function ($row) {
                    return sprintf('%02d:00 - %02d:00', $row['hour'], $row['hour'] + 1);
                }
            ],
            [
                'label' => "Категория эфира",
                'attribute' => 'category',
                'footer' => 'Итого<br>Маржа'
            ],
        ];

        $categories = $this->getCategories();
        $categoriesTotal = [];
        $marginTotal = [];

        foreach ($categories as $category) {
            $categoriesTotal[$category->id] = array_reduce($dataProvider->models, function ($total, $row) use ($category) {
                return $total + ($row['sum_' . $category->id] ?? 0);
            }, 0);

            $marginTotal[$category->id] = array_reduce($dataProvider->models, function ($total, $row) use ($category) {
                return $total + ($row['margin_' . $category->id] ?? 0);
            }, 0);

            $columns[] = [
                'label' => $category->name,
                'value' => function ($row) use ($category) {
                    return \Yii::$app->formatter->asDecimal(round($row['sum_' . $category->id] ?? 0));
                },
                'footer' =>
                    \Yii::$app->formatter->asDecimal(round($categoriesTotal[$category->id]))
                    . '<br>'
                    . \Yii::$app->formatter->asDecimal(round($marginTotal[$category->id]))
            ];
        }

        return $columns;
    }

    /**
     * Список категорий для дополнительного отчета
     * @return \skeeks\cms\models\Tree[]
     */
    public function getCategories()
    {
        return TreeList::getTreeById(TreeList::CATALOG_ID)->getChildren()->onCondition(['active' => Cms::BOOL_Y])->all();
    }

    /**
     * считает сумму плана
     * @param \yii\data\BaseDataProvider $dataProvider
     * @return float|int
     */
    public function getSumPlan(\yii\data\BaseDataProvider $dataProvider)
    {
        $models = $dataProvider->getModels();
        return array_sum(array_column($models, 'sum_plan'));
    }

    /**
     * считает фактическую сумму
     * @param \yii\data\BaseDataProvider $dataProvider
     * @return float|int
     */
    public function getSumFact(\yii\data\BaseDataProvider $dataProvider)
    {
        $models = $dataProvider->getModels();
        return array_sum(array_column($models, 'sum_fact'));
    }

    /**
     * Считает сумму эфира по данным битрикс
     * @return array
     */
    public function getTotalSum()
    {
        return PlanHelper::getTotalSum(date('Y-m-d H:i:s', $this->getPeriodBegin($this->date)), date('Y-m-d H:i:s', $this->getPeriodEnd($this->date)));
    }

    /**
     * считает накопленную сумму эфира
     * @param $totalSum
     * @return mixed
     */
    public function getTotalSumAccFromTotalSum($totalSum)
    {
        $prevKey = null;
        foreach ($totalSum as $key => $value) {
            if ($prevKey) {
                $totalSum[$key] += $totalSum[$prevKey];
            }
            $prevKey = $key;
        }
        return $totalSum;
    }

    /**
     * Форматирует данные по плану для графика
     * @param \yii\data\BaseDataProvider $dataProvider
     *
     * @return array
     */
    public function getDataPlan(\yii\data\BaseDataProvider $dataProvider)
    {
        return $this->getDataColumn($dataProvider, 'sum_plan');
    }

    /**
     * Форматирует данные по факту для графика
     * @param \yii\data\BaseDataProvider $dataProvider
     *
     * @return array
     */
    public function getDataFact(\yii\data\BaseDataProvider $dataProvider)
    {
        return $this->getDataColumn($dataProvider, 'sum_fact');
    }

    /**
     * Форматирует данные по столбцу для графика
     * @param \yii\data\BaseDataProvider $dataProvider
     * @param $column
     *
     * @return array
     */
    public function getDataColumn(\yii\data\BaseDataProvider $dataProvider, $column)
    {
        $result = [];
        $models = $dataProvider->getModels();
        foreach ($models as $row) {
            $result[] = ['x' => $this->getPeriodBegin($this->date) + (int)$row['hour'] * HOUR_1, 'y' => $row[$column]];
        }
        return $result;
    }

    /**
     * Вычисляет последний полный час для отчета
     * @return false|int|string
     */
    public function getLastHour()
    {
        if ($this->isCurDay()) {
            return date('G') - 1;
        }
        return 23;
    }

    /**
     * Свежий ли отчет (true), или вчерашний (false)
     * @return bool
     */
    public function isCurDay()
    {
        return date('Y-m-d') == $this->date;
    }

    /**
     * Первый отчет в неделе за прошедший период
     * @return bool
     */
    public function isWeekBegin()
    {
        return date('w') == 1 && !$this->isCurDay();
    }

    /**
     * Суммарные данные по марже
     * @return number
     */
    public function getMarginSummary()
    {
        $query = <<<SQL
SELECT 
  sum((sb.price - if(margin.price IS NULL OR margin.price = '' OR margin.price = 0, sb.price, margin.price)) * sb.quantity) sum_margin
FROM shop_order so
LEFT JOIN shop_basket sb ON sb.order_id = so.id
LEFT JOIN shop_product_price margin ON margin.product_id = sb.main_product_id AND margin.type_price_id = :purchase_property_id
WHERE so.created_at >= :begin_datetime AND so.created_at <= :end_datetime
SQL;


        $margin = \Yii::$app->db->createCommand($query, [
            ':begin_datetime' => $this->getPeriodBegin($this->date),
            ':end_datetime' => $this->getPeriodEnd($this->date),
            ':purchase_property_id' => PlanHelper::PURCHASE_PROPERTY_ID
        ])->queryScalar();

        $margin = $this->getGoodSum($margin);
        return $margin;
    }

    /**
     * Суммарные данные по заказам, сгруппированные по источникам
     * @return array
     */
    public function getOrdersSummary()
    {
        $ordersQuery = ShopOrder::find()
            ->select(new \yii\db\Expression("source, source_detail, SUM(price + IF(source = '" . ShopOrder::SOURCE_SITE . "', " . self::PRICE_DELIVERY_SITE . ", 0)) as price_sum, 
                COUNT(*) AS amount"))
            ->where(['BETWEEN', 'created_at', $this->getPeriodBegin($this->date), $this->getPeriodEnd($this->date)])
            ->groupBy('source, source_detail');

        $ordersData = $ordersQuery->asArray()->all();

        foreach ($ordersData as &$order) {
            $order['price_sum'] = $this->getGoodSum($order['price_sum']);
        }

        return $ordersData;
    }

    /**
     * Считает сумму заказов по фильтрам источника
     * @param $ordersSummary
     * @param string $source
     * @param string $sourceDetail
     * @return float|int
     */
    public function getOrdersSummarySum(array $ordersSummary, $source = '', $sourceDetail = '')
    {
        $ordersSummaryFiltered = array_filter($ordersSummary, function ($row) use ($source, $sourceDetail) {
            if ($source && $source != $row['source']) {
                return false;
            }
            if ($sourceDetail && $sourceDetail != $row['source_detail']) {
                return false;
            }
            return true;
        });

        return array_sum(array_column($ordersSummaryFiltered, 'price_sum'));
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
     * Отдает данные для отрисовки графика в highcharts
     * @param \yii\data\BaseDataProvider $dataProvider
     * @return array
     */
    public function getHighchartsData(\yii\data\BaseDataProvider $dataProvider)
    {
        $series = [
            [
                'name' => 'Продажи, руб.',
                'data' => (new SeriesDataHelper($this->getDataFact($dataProvider), ['x:timestamp', 'y:int']))->process(),
            ],
            [
                'name' => 'План, руб.',
                'data' => (new SeriesDataHelper($this->getDataPlan($dataProvider), ['x:timestamp', 'y:int']))->process(),
                'color' => '#910000'
            ],
        ];
        return $this->getHighchartsTemplate('Соотношение продаж с планом', $series);
    }

    /**
     * Отдает данные для отрисовки графика продаж с сайта в highcharts
     * @param \yii\data\BaseDataProvider $dataProvider
     * @param string $title
     * @return array
     */
    public function getHighchartsDataForSource(\yii\data\BaseDataProvider $dataProvider, $title = 'Продажи сайта')
    {
        $series = [
            [
                'name' => 'Продажи, руб.',
                'data' => new SeriesDataHelper($this->getDataFact($dataProvider), ['x:timestamp', 'y:int']),
            ],
        ];
        return $this->getHighchartsTemplate($title, $series);
    }

    /**
     * Отдает данные для отрисовки графика по трафику на основании данных из GA
     * @return array|null
     */
    public function getHighchartsDataForTrafic()
    {
        $series = [
            [
                'name' => 'Сеансы, шт.',
                'data' => new SeriesDataHelper($this->getGaSessionsData(), ['x:timestamp', 'y:int']),
                'yAxis' => 0,
            ],
            [
                'name' => 'Заказы, шт.',
                'data' => new SeriesDataHelper($this->getOrdersData(), ['x:timestamp', 'y:int']),
                'yAxis' => 1,
                'color' => '#910000'
            ],
            [
                'name' => 'Заказы с телефона 8 800 7752250, шт.',
                'data' => new SeriesDataHelper($this->getOrdersData(ShopOrder::SOURCE_DETAIL_PHONE1), ['x:timestamp', 'y:int']),
                'yAxis' => 1,
                'color' => '#009100'
            ],
            [
                'name' => 'Заказы с телефона 8 800 3016010, шт.',
                'data' => new SeriesDataHelper($this->getOrdersData(ShopOrder::SOURCE_DETAIL_PHONE2), ['x:timestamp', 'y:int']),
                'yAxis' => 1,
                'color' => '#910091'
            ],
        ];
        $config =
            [
                'options' =>
                    [
                        'yAxis' => [
                            [
                                'title' => ['text' => 'Сеансы, шт'],
                                'labels' => [
                                    'align' => 'left',
                                    'x' => 3,
                                ],
                            ],
                            [
                                'title' => [
                                    'text' => 'Заказы, шт',
                                    'style' => [
                                        'color' => '#910000'
                                    ]
                                ],
                                'labels' => [
                                    'align' => 'right',
                                    'x' => -3,
                                    'style' => [
                                        'color' => '#910000'
                                    ]
                                ],
                                'opposite' => true,
                            ]
                        ]
                    ]
            ];
        return $this->getHighchartsTemplate('Трафик', $series, $config);
    }

    /**
     * @param null $treeId
     * @return array
     */
    public function getHighchartsDataOnAir($treeId = null)
    {
        $series = [
            [
                'name' => 'Продажи товаров из эфира, руб.',
                'data' => new SeriesDataHelper($this->getBasketProductSalesSeries($treeId, true), ['x:timestamp', 'y:int']),
            ],
            [
                'name' => 'Продажи товаров НЕ из эфира, руб.',
                'data' => new SeriesDataHelper($this->getBasketProductSalesSeries($treeId, false), ['x:timestamp', 'y:int']),
                'color' => '#910000'
            ],
        ];

        if ($this->showCts) {
            $series[] = [
                'name' => 'Продажи товара ЦТС, руб.',
                'data' => new SeriesDataHelper($this->getBasketCtsSalesSeries(), ['x:timestamp', 'y:int']),
                'color' => '#009100'
            ];
        }

        $options = [
            'options' => [
                'xAxis' => [
                    'categories' => $this->getOnairCategories(),
                    'labels' => [
                        'rotation' => 0
                    ]
                ]
            ]
        ];

        return $this->getHighchartsTemplate('Продажи товаров из эфира', $series, $options);
    }

    /**
     * @param array $options
     * @return string
     */
    public function getHighchartExportUrl(array $options)
    {
        $zoneOffset = date('Z');

        $options['async'] = true;
        $options['type'] = 'jpeg';
        $options['width'] = 800;

        $options['options']['chart']['height'] = 300;
        $options['options']['plotOptions']['line']['dataLabels']['style']['fontSize'] = 5;
        $options['options']['xAxis']['labels']['style']['fontSize'] = 6;
        $options['globaloptions']['global']['timezoneOffset'] = -1 * $zoneOffset / 60;
        //$options['options']['yAxis']['labels']['style']['fontSize'] = 5;

        $image = $this->convertHighchartsToImage($options);

        return $image;
    }

    /**
     * @param array $options
     * @return string
     */
    private function convertHighchartsToImage(array $options)
    {
        $exportUrl = 'http://export.highcharts.com/';
        $client = new Client();

        /** @var \yii\httpclient\Response $response */
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl($exportUrl)
            ->addHeaders(['Content-Type' => 'application/json'])
            ->setContent(Json::encode($options))
            ->send();

        if ($response->isOk) {
            return $this->saveChart($exportUrl . $response->content, substr($response->content, 7));
        }

        return $response->content;
    }

    /**
     * @param $exportUrl
     * @param $chartsName
     * @return string
     * @throws \yii\base\Exception
     */
    private function saveChart($exportUrl, $chartsName)
    {
        $chartsDir = '/uploads/charts/' . date('Y-m') . '/';
        $exportDir = \Yii::getAlias('@frontend/web') . $chartsDir;

        if (!file_exists($exportDir)) {
            \yii\helpers\FileHelper::createDirectory($exportDir);
        }

        if (@copy($exportUrl, $exportDir . $chartsName)) {
            $chartsUrl = \Yii::$app->urlManager->baseUrl . $chartsDir . $chartsName;
            return "<img src='{$chartsUrl}'>";
        }

        $errors = error_get_last();
        return 'Не удалось создать график: ' . $errors['message'];
    }

    /**
     * @return mixed|array
     */
    public function getOnairBlocks()
    {
        return AirBlock::getScheduleListForPeriod($this->getPeriodBegin($this->date), $this->getPeriodEnd($this->date));
    }

    /**
     * Формирует список категорий эфира
     * @param bool $fillToFullDay
     * @return array
     */
    public function getOnairCategories($fillToFullDay = true)
    {
        // [k => v], k => milliseconds every hour, v => time with/wo name
        $categories = [];

        $airBlocks = $this->getOnairBlocks();
        foreach ($airBlocks as $airBlock) {
            $categories[$airBlock['begin_datetime'] * 1000] = $airBlock['begin_time'] . '<br>' . $airBlock['name'];
        }

        // дополняем оставшиеся категории до 24 часов заглушками без названия категории
        if ($fillToFullDay) {
            for ($i = sizeof($categories); $i < 24; $i++) {
                $index = 1000 * (strtotime($this->date) + $i * 3600 + $this->dateTimeOffset);
                $hour = $i + $this->dateTimeOffset / HOUR_1;
                if ($hour > 23) $hour -= 24;
                $categories[$index] = sprintf('%02d:00', $hour);
            }
        }

        return $categories;
    }

    /**
     * @param $treeId
     * @param null $isOnAir
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function getBasketProductSalesSeries($treeId, $isOnAir = null)
    {
        $shopBaskets = $this->getBasketProductSalesData($treeId, $isOnAir);

        $shopBasketsData = [];
        foreach ($shopBaskets as $row) {
            $shopBasketsData[] = ['x' => $row['order_date'], 'y' => $row['product_price']];
        }

        return $shopBasketsData;
    }

    /**
     * @param $treeId
     * @param bool $isOnAir
     * @return array|AirDayProductTime[]|ShopBasket[]|ShopOrder[]|\yii\db\ActiveRecord[]
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function getBasketProductSalesData($treeId, $isOnAir = null)
    {
        $onAirProducts = $this->getOnairProducts();

        $shopBasketsQuery = ShopBasket::find()
            ->select(new \yii\db\Expression('SUM(shop_basket.price * shop_basket.quantity) as product_price, UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(shop_order.created_at), "%Y-%m-%d %H:00:00")) as order_date'))
            ->innerJoin('shop_order', 'shop_order.id = order_id')
            ->where(['BETWEEN', 'shop_order.created_at', $this->getPeriodBegin($this->date), $this->getPeriodEnd($this->date)])
            ->andWhere(['shop_basket.has_removed' => ShopBasket::HAS_REMOVED_FALSE])
            ->andWhere(['NOT', ['shop_basket.order_id' => null]]);

        if ($isOnAir === true) {
            $shopBasketsQuery->andWhere(['main_product_id' => array_keys($onAirProducts)]);
        } elseif ($isOnAir === false) {
            $shopBasketsQuery->andWhere(['NOT', ['main_product_id' => array_keys($onAirProducts)]]);
        }

        if ($this->showCts) {
            /** @var SsShare $cts */
            $cts = \modules\shopandshow\lists\Shares::getCtsProduct($this->date);
            if ($cts) {
                $shopBasketsQuery->andWhere(['NOT', ['main_product_id' => $cts->product->id]]);
            }
        }

        if ($treeId) {
            $tree = \common\lists\TreeList::getTreeById($treeId);
            $trees = array_merge([$treeId], $tree->getDescendants()->select('id')->asArray()->column());

            $shopBasketsQuery
                ->innerJoin('cms_content_element', 'cms_content_element.id = main_product_id')
                ->andWhere(['tree_id' => $trees]);
        }

        return $shopBasketsQuery
            ->groupBy('order_date')
            ->orderBy('order_date')
            ->asArray()
            ->all();
    }

    /**
     * @return array|AirDayProductTime[]|ShopBasket[]|ShopOrder[]|\yii\db\ActiveRecord[]
     */
    public function getBasketCtsSales()
    {
        /** @var SsShare $cts */
        $cts = \modules\shopandshow\lists\Shares::getCtsProduct($this->date);

        if (!$cts) {
            return [];
        }

        $shopBasketsQuery = ShopBasket::find()
            ->innerJoin('shop_order', 'shop_order.id = order_id')
            ->select(new \yii\db\Expression('SUM(shop_basket.price * shop_basket.quantity) as product_price, UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(shop_order.created_at), "%Y-%m-%d %H:00:00")) as order_date'))
            ->where(['BETWEEN', 'shop_order.created_at', $this->getPeriodBegin($this->date), $this->getPeriodEnd($this->date)])
            ->andWhere(['shop_basket.has_removed' => ShopBasket::HAS_REMOVED_FALSE])
            ->andWhere(['NOT', ['shop_basket.order_id' => null]])
            ->andWhere(['main_product_id' => $cts->product->id]);

        $shopBaskets = $shopBasketsQuery
            ->groupBy('order_date')
            ->orderBy('order_date')
            ->asArray()
            ->all();

        return $shopBaskets;
    }

    /**
     * @return array
     */
    public function getBasketCtsSalesSeries()
    {
        $shopBaskets = $this->getBasketCtsSales();

        $shopBasketsData = [];
        foreach ($shopBaskets as $row) {
            $shopBasketsData[] = ['x' => $row['order_date'], 'y' => $row['product_price']];
        }

        return $shopBasketsData;
    }


    /**
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function getOnairProducts()
    {
        return \Yii::$app->db->cache(function () {
            return AirDayProductTime::find()
                ->innerJoin('ss_mediaplan_air_blocks b')
                ->andWhere('b.begin_datetime >= :begin_datetime AND b.begin_datetime <= :end_datetime ', [
                    ':begin_datetime' => $this->getPeriodBegin($this->date),
                    ':end_datetime' => $this->getPeriodEnd($this->date),
                ])
                ->select('lot_id')
                ->indexBy('lot_id')
                ->groupBy('lot_id')
                ->asArray()
                ->all();
        }, MIN_15);
    }


    /**
     * считает накопленные значения для данных
     * @param \yii\data\BaseDataProvider $dataProvider
     */
    private function calcAccValues(\yii\data\BaseDataProvider $dataProvider)
    {
        $models = $dataProvider->getModels();
        foreach ($models as $i => &$row) {
            if ($i == 0) {
                $row['sum_plan_acc'] = $row['sum_plan'];
                $row['sum_fact_acc'] = $row['sum_fact'];
                continue;
            }

            $row['sum_plan_acc'] = $models[$i - 1]['sum_plan_acc'] + $row['sum_plan'];
            $row['sum_fact_acc'] = $models[$i - 1]['sum_fact_acc'] + $row['sum_fact'];
        }
        $dataProvider->setModels($models);
    }

    /**
     * @param \yii\data\BaseDataProvider $dataProvider
     */
    private function calcCategoryValues(\yii\data\BaseDataProvider $dataProvider)
    {
        $result = [];
        $models = $dataProvider->getModels();
        foreach ($models as $row) {
            if (!isset($result[$row['hour']])) {
                $result[$row['hour']] = [
                    'hour' => $row['hour'],
                    'category' => $row['category']
                ];
            }
            $result[$row['hour']]['sum_' . $row['parent_tree_id']] = $row['sum_category'];
            $result[$row['hour']]['margin_' . $row['parent_tree_id']] = $row['sum_margin_category'];
        }
        $dataProvider->setModels($result);
    }

    /**
     * Данные по сеансам из GA, сгруппированные по часу
     * @return array
     */
    private function getGaSessionsData()
    {
        $ga = new GoogleAnalytics($this->date);
        $gaData = $ga->getSessions();

        if (!$gaData['sessions']) {
            return [];
        }

        $sessionsData = [];
        foreach ($gaData['sessions']['dataTable']['rows'] as $row) {
            $sessionsData[] = ['x' => $this->getPeriodBegin($this->date) + (int)$row['c'][0]['v'] * HOUR_1, 'y' => $row['c'][1]['v']];
        }

        return $sessionsData;
    }

    /**
     * Данные по кол-ву заказов, сгруппированные по часу
     * @param string $source_detail
     * @return array
     */
    private function getOrdersData($source_detail = null)
    {
        $ordersQuery = ShopOrder::find()
            ->select(new \yii\db\Expression('UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(created_at), "%Y-%m-%d %H:00:00")) as order_date, count(*) as amount'))
            ->where(['BETWEEN', 'created_at', $this->getPeriodBegin($this->date), $this->getPeriodEnd($this->date)])
            ->groupBy('order_date')
            ->orderBy('order_date');

        if (!empty($source_detail)) {
            $ordersQuery->andWhere(['source_detail' => $source_detail]);
        }

        $orders = $ordersQuery->asArray()->all();

        $ordersData = [];
        foreach ($orders as $row) {
            $ordersData[] = ['x' => $row['order_date'], 'y' => $row['amount']];
        }

        return $ordersData;
    }

    /**
     * Данные по стоимости доставки заказов, сгруппированные по часу
     * @return array
     */
    public function getOrdersDeliveryData()
    {
        $ordersQuery = ShopOrder::find()
            ->select(new \yii\db\Expression('UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(created_at), "%Y-%m-%d %H:00:00")) AS order_date, 
                SUM(IF(source = "' . ShopOrder::SOURCE_SITE . '", ' . self::PRICE_DELIVERY_SITE . ', 0)) AS delivery_price'))
            ->where(['BETWEEN', 'created_at', $this->getPeriodBegin($this->date), $this->getPeriodEnd($this->date)])
            ->groupBy('order_date')
            ->orderBy('order_date');

        $orders = $ordersQuery->asArray()->all();

        return $orders;
    }

    /**
     * Накручивает коэффициент на указанные столбцы
     * @param \yii\data\BaseDataProvider $dataProvider
     * @param array $columns
     */
    private function adjustDataProdiverByFactor(\yii\data\BaseDataProvider $dataProvider, $columns = [])
    {
        $models = $dataProvider->getModels();
        foreach ($models as &$model) {
            foreach ($columns as $column) {
                $model[$column] = $this->getGoodSum($model[$column]);
            }
        }
        $dataProvider->setModels($models);
    }

    /**
     * Считает правильную сумму, учитывая волшебный коэффициент
     * @param $sum
     * @return float
     */
    private function getGoodSum($sum)
    {
        return $sum + $sum * $this->factor;
    }

    private function getPeriodBegin($date)
    {
        return strtotime(sprintf('%s 00:00:00', $date)) + $this->dateTimeOffset;
    }

    private function getPeriodEnd($date)
    {
        return strtotime(sprintf('%s 23:59:59', $date)) + $this->dateTimeOffset;
    }
}