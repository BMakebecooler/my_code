<?php

use miloschuman\highcharts\SeriesDataHelper;
use modules\shopandshow\models\statistic\Statistics;
use common\helpers\Dates;
/* @var $this yii\web\View */
/* @var $model \modules\shopandshow\models\statistic\StatisticsForm */
/* @var $cmsContentElement \common\models\cmsContent\CmsContentElement */

$timestamp = $model->timestamp;
$zoneOffset = date('Z');
$beginEfirForGraph = (Dates::beginOfDate($model->timestamp)) * 1000;
$endEfirForGraph = (Dates::endOfDate($model->timestamp)) * 1000;

// Смещение часового пояса в минутах от GMT
$this->registerJs(<<<JS
Highcharts.setOptions({
    global: {
        timezoneOffset: -1*{$zoneOffset}/60
    }
});
JS
);

?>
<div class="row">
    <div class="graph-views col-xs-3">
    <?= \miloschuman\highcharts\Highcharts::widget([
        'options' => [
            'chart' => [
                'zoomType' => 'x',
                'type' => 'column'
            ],
            'title' => ['text' => 'Трафик в карточку'],
            'xAxis' => [
                'type' => 'datetime',
                'min' => $beginEfirForGraph,
                'max' => $endEfirForGraph,
                'tickInterval' => 3600 * 1000 // 1 hour
            ],
            'yAxis' => [
                'title' => ['text' => 'Просмотров']
            ],
            'legend' => [
                'enabled' => false
            ],
            'series' => [[
                'name' => 'Просмотров',
                'data' => new SeriesDataHelper(Statistics::getCountViewedData($cmsContentElement->id, $timestamp), ['x:timestamp', 'y:int'])
            ]]
        ]
    ]);
    ?>
    </div>

    <div class="graph-basket col-xs-3">
        <?= \miloschuman\highcharts\Highcharts::widget([
            'options' => [
                'chart' => [
                    'zoomType' => 'x',
                    'type' => 'column'
                ],
                'title' => ['text' => 'Добавление в корзину'],
                'xAxis' => [
                    'type' => 'datetime',
                    'min' => $beginEfirForGraph,
                    'max' => $endEfirForGraph,
                    'tickInterval' => 3600 * 1000 // 1 hour
                ],
                'yAxis' => [
                    'title' => ['text' => 'Добавлений']
                ],
                'legend' => [
                    'enabled' => false
                ],
                'series' => [[
                    'name' => 'Добавлений',
                    'data' => new SeriesDataHelper(Statistics::getCountBasketData($cmsContentElement->id, $timestamp), ['x:timestamp', 'y:int'])
                ]]
            ]
        ]);
        ?>
    </div>

    <? /*
    <div class="graph-convercy col-xs-3">
        <?= \miloschuman\highcharts\Highcharts::widget([
            'options' => [
                'chart' => [
                    'zoomType' => 'x',
                    'type' => 'column'
                ],
                'title' => ['text' => 'Конверсия добавления в корзину'],
                'xAxis' => [
                    'type' => 'datetime',
                    'min' => $beginEfirForGraph,
                    'max' => $endEfirForGraph,
                    'tickInterval' => 3600 * 1000 // 1 hour
                ],
                'yAxis' => [
                    'title' => ['text' => 'Конверсия, %']
                ],
                'legend' => [
                    'enabled' => false
                ],
                'series' => [[
                    'name' => 'Конверсия, %',
                    'data' => new SeriesDataHelper(Statistics::getBasketConvercyData($cmsContentElement->id, $timestamp), ['x:timestamp', 'y:int'])
                ]]
            ]
        ]);
        ?>
    </div>
    */ ?>

    <div class="graph-basket col-xs-3">
        <?= \miloschuman\highcharts\Highcharts::widget([
            'options' => [
                'chart' => [
                    'zoomType' => 'x',
                    'type' => 'column'
                ],
                'title' => ['text' => 'Оформление заказа'],
                'xAxis' => [
                    'type' => 'datetime',
                    'min' => $beginEfirForGraph,
                    'max' => $endEfirForGraph,
                    'tickInterval' => 3600 * 1000 // 1 hour
                ],
                'yAxis' => [
                    'title' => ['text' => 'Оформлений']
                ],
                'legend' => [
                    'enabled' => false
                ],
                'series' => [[
                    'name' => 'Добавлений',
                    'data' => new SeriesDataHelper(Statistics::getCountOrderData($cmsContentElement->id, $timestamp), ['x:timestamp', 'y:int'])
                ]]
            ]
        ]);
        ?>
    </div>

    <div class="graph-convercy col-xs-3">
        <?= \miloschuman\highcharts\Highcharts::widget([
            'options' => [
                'chart' => [
                    'zoomType' => 'x',
                    'type' => 'column'
                ],
                'title' => ['text' => 'Конверсия'],
                'xAxis' => [
                    'type' => 'datetime',
                    'min' => $beginEfirForGraph,
                    'max' => $endEfirForGraph,
                    'tickInterval' => 3600 * 1000 // 1 hour
                ],
                'yAxis' => [
                    [
                        'title' => ['text' => 'Конверсия корзины, %'],
                        'labels' => [
                            'align' => 'left',
                            'x' => 3,
                            //y: 16,
                            //format: '{value:.,0f}'
                        ],
                    ],
                    [
                        'title' => [
                            'text' => 'Конверсия заказов, %',
                            'style' => [
                                'color' => '#910000'
                            ]
                        ],
                        'labels' => [
                            'align' => 'right',
                            'x' => -3,
                            //y: 16,
                            //format: '{value:.,0f}'
                            'style' => [
                                'color' => '#910000'
                            ]
                        ],
                        //'linkedTo' => 0,
                        'gridLineWidth' => 0,
                        'opposite' => true,
                    ],
                ],
                'legend' => [
                    'enabled' => false
                ],
                'series' => [
                    [
                        'name' => 'Конверсия корзины, %',
                        'data' => new SeriesDataHelper(Statistics::getBasketConvercyData($cmsContentElement->id, $timestamp), ['x:timestamp', 'y:int']),
                        'yAxis' => 0
                    ],
                    [
                        'name' => 'Конверсия заказов, %',
                        'data' => new SeriesDataHelper(Statistics::getOrderConvercyData($cmsContentElement->id, $timestamp), ['x:timestamp', 'y:int']),
                        'yAxis' => 1,
                        'color' => '#910000'
                    ],
                ]
            ]
        ]);
        ?>
    </div>
</div>

<div class="row">
    <div class="graph-views-summary col-xs-3">
        <?= \miloschuman\highcharts\Highcharts::widget([
            'options' => [
                'chart' => [
                    'zoomType' => 'x'
                ],
                'title' => ['text' => 'Трафик в карточку накопленный'],
                'xAxis' => [
                    'type' => 'datetime',
                    'min' => $beginEfirForGraph,
                    'max' => $endEfirForGraph,
                    'tickInterval' => 3600 * 1000 // 1 hour
                ],
                'yAxis' => [
                    'title' => ['text' => 'Просмотров']
                ],
                'legend' => [
                    'enabled' => false
                ],
                'series' => [[
                    'name' => 'Просмотров',
                    'data' => new SeriesDataHelper(Statistics::getCountViewedSummaryData($cmsContentElement->id, $timestamp), ['x:timestamp', 'y:int'])
                ]]
            ]
        ]);
        ?>
    </div>

    <div class="graph-basket-summary col-xs-3">
        <?= \miloschuman\highcharts\Highcharts::widget([
            'options' => [
                'chart' => [
                    'zoomType' => 'x'
                ],
                'title' => ['text' => 'Добавление в корзину накопленное'],
                'xAxis' => [
                    'type' => 'datetime',
                    'min' => $beginEfirForGraph,
                    'max' => $endEfirForGraph,
                    'tickInterval' => 3600 * 1000 // 1 hour
                ],
                'yAxis' => [
                    'title' => ['text' => 'Добавлений']
                ],
                'legend' => [
                    'enabled' => false
                ],
                'series' => [[
                    'name' => 'Добавлений',
                    'data' => new SeriesDataHelper(Statistics::getCountBasketSummaryData($cmsContentElement->id, $timestamp), ['x:timestamp', 'y:int'])
                ]]
            ]
        ]);
        ?>
    </div>

    <? /*
    <div class="graph-convercy-summary col-xs-3">
        <?= \miloschuman\highcharts\Highcharts::widget([
            'options' => [
                'chart' => [
                    'zoomType' => 'x'
                ],
                'title' => ['text' => 'Конверсия накопленная'],
                'xAxis' => [
                    'type' => 'datetime',
                    'min' => $beginEfirForGraph,
                    'max' => $endEfirForGraph,
                    'tickInterval' => 3600 * 1000 // 1 hour
                ],
                'yAxis' => [
                    'title' => ['text' => 'Конверсия, %']
                ],
                'legend' => [
                    'enabled' => false
                ],
                'series' => [[
                    'name' => 'Конверсия, %',
                    'data' => new SeriesDataHelper(Statistics::getBasketConvercySummaryData($cmsContentElement->id, $timestamp), ['x:timestamp', 'y:int'])
                ]]
            ]
        ]);
        ?>
    </div>
 */ ?>

    <div class="graph-basket-summary col-xs-3">
        <?= \miloschuman\highcharts\Highcharts::widget([
            'options' => [
                'chart' => [
                    'zoomType' => 'x'
                ],
                'title' => ['text' => 'Оформление заказа накопленное'],
                'xAxis' => [
                    'type' => 'datetime',
                    'min' => $beginEfirForGraph,
                    'max' => $endEfirForGraph,
                    'tickInterval' => 3600 * 1000 // 1 hour
                ],
                'yAxis' => [
                    'title' => ['text' => 'Оформлений']
                ],
                'legend' => [
                    'enabled' => false
                ],
                'series' => [[
                    'name' => 'Добавлений',
                    'data' => new SeriesDataHelper(Statistics::getCountOrderSummaryData($cmsContentElement->id, $timestamp), ['x:timestamp', 'y:int'])
                ]]
            ]
        ]);
        ?>
    </div>

    <div class="graph-convercy-summary col-xs-3">
        <?= \miloschuman\highcharts\Highcharts::widget([
            'options' => [
                'chart' => [
                    'zoomType' => 'x'
                ],
                'title' => ['text' => 'Конверсия накопленная'],
                'xAxis' => [
                    'type' => 'datetime',
                    'min' => $beginEfirForGraph,
                    'max' => $endEfirForGraph,
                    'tickInterval' => 3600 * 1000 // 1 hour
                ],
                'yAxis' => [
                    [
                        'title' => [
                            'text' => 'Накопленная конверсия корзины, %',
                        ],
                        'labels' => [
                            'align' => 'left',
                            'x' => 3,
                            //y: 16,
                            //format: '{value:.,0f}'
                        ],
                    ],
                    [
                        'title' => [
                            'text' => 'Накопленная конверсия заказов, %',
                            'style' => [
                                'color' => '#910000'
                            ]
                        ],
                        'labels' => [
                            'align' => 'right',
                            'x' => -3,
                            //y: 16,
                            //format: '{value:.,0f}'
                            'style' => [
                                'color' => '#910000'
                            ]
                        ],
                        //'linkedTo' => 0,
                        'gridLineWidth' => 0,
                        'opposite' => true,
                    ],
                ],
                'legend' => [
                    'enabled' => false
                ],
                'series' => [
                    [
                        'name' => 'Конверсия корзины, %',
                        'data' => new SeriesDataHelper(Statistics::getBasketConvercySummaryData($cmsContentElement->id, $timestamp), ['x:timestamp', 'y:int']),
                        'yAxis' => 0
                    ],
                    [
                        'name' => 'Конверсия заказов, %',
                        'data' => new SeriesDataHelper(Statistics::getOrderConvercySummaryData($cmsContentElement->id, $timestamp), ['x:timestamp', 'y:int']),
                        'yAxis' => 1,
                        'color' => '#910000'
                    ],
                ]
            ]
        ]);
        ?>
    </div>

</div>