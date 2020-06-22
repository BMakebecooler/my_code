<?php

use miloschuman\highcharts\Highcharts;
use modules\shopandshow\models\shop\stock\forms\SalesStockPeriod;
use yii\bootstrap\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model SalesStockPeriod */

$this->title = 'Продажи стока по периодам';

$stockSales = $model->getBasketStockSalesByPeriod();

$stockSalesForChartPrice = $model->prepareHighChartsSeriesByPeriod($stockSales);
$stockSalesForChartQuantity = $model->prepareHighChartsSeriesByPeriod($stockSales, 'quantity');
$stockSalesForChartPartPrice = $model->prepareHighChartsSeriesByPeriod($stockSales, 'stock_part_by_price');
$stockSalesForChartPartQuantity = $model->prepareHighChartsSeriesByPeriod($stockSales, 'stock_part_by_quantity');

$totals = $model->getTotals(true);

$totalPrice = \Yii::$app->formatter->asDecimal($totals['total']);
$totalStockPrice = \Yii::$app->formatter->asDecimal($totals['stock']);

$totalQuantity = \Yii::$app->formatter->asInteger($totals['total_quantity']);
$totalStockQuantity = \Yii::$app->formatter->asInteger($totals['stock_quantity']);

$totalsStockRatioPrice = $totals['total'] ? \Yii::$app->formatter->asPercent($totals['stock'] / $totals['total'], 2) : '&mdash;';

$totalsStockRatioQuantity = $totals['total_quantity']
    ? \Yii::$app->formatter->asPercent($totals['stock_quantity'] / $totals['total_quantity'], 2) : '&mdash;';
?>

<div class="h3">Продажи стока по периодам</div>

<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => false,
    'method' => 'GET',
    'action' => '/'.\Yii::$app->request->pathInfo
]); ?>
<input type="hidden" name="scroll-to-onair-graph" id="scroll-to-onair-graph" value="0">

<?= $form->field($model, 'dateFrom')->widget(
    \kartik\date\DatePicker::class,
    [
        'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
        ]
    ]
); ?>

<?= $form->field($model, 'dateTo')->widget(
    \kartik\date\DatePicker::class,
    [
        'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
        ]
    ]
); ?>

<?= Html::submitButton("Показать", [
    'class' => 'btn btn-primary',
    'name' => 'show',
    'value' => 1
]); ?>

<hr>

<?php
$zoneOffset = date('Z');
// Смещение часового пояса в минутах от GMT
$this->registerJs(<<<JS
Highcharts.setOptions({
    global: {
        timezoneOffset: -1*{$zoneOffset}/60
    }
});
JS
);

$hcOptionsQuantity = [
    'options' => [
        'yAxis' => [
            [
                'title' => ['text' => 'Сумма, штук.'],
            ]
        ],
    ]
];

$hcOptionsPart = [
    'options' => [
        'yAxis' => [
            [
                'title' => ['text' => 'Доля, %'],
            ]
        ],
    ]
];

?>

<div class="well">
    <table class="table" style="width: auto;">
        <thead>
        <tr>
            <th>За период</th>
            <th>Сток</th>
            <th>Всего</th>
            <th>Доля стока</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th>Кол-во, шт.</th>
            <td><?= $totalStockQuantity; ?></td>
            <td><?= $totalQuantity ?></td>
            <td><?= $totalsStockRatioQuantity; ?></td>
        </tr>
        <tr>
            <th>Доход, руб.</th>
            <td><?= $totalStockPrice; ?></td>
            <td><?= $totalPrice; ?></td>
            <td><?= $totalsStockRatioPrice; ?></td>
        </tr>
        </tbody>
    </table>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <? echo Highcharts::widget($model->getHighchartsDataByPeriod($stockSalesForChartPrice, 'Продажи, руб.')); ?>
        </div>
        <div class="col-md-6">
            <? echo Highcharts::widget($model->getHighchartsDataByPeriod($stockSalesForChartPartPrice, 'Доля в доходе, %', $hcOptionsPart)); ?>
        </div>
        <div class="col-md-6">
            <? echo Highcharts::widget($model->getHighchartsDataByPeriod($stockSalesForChartQuantity, 'Продажи, шт.', $hcOptionsQuantity)); ?>
        </div>
        <div class="col-md-6">
            <? echo Highcharts::widget($model->getHighchartsDataByPeriod($stockSalesForChartPartQuantity, 'Доля в кол-ве %', $hcOptionsPart)); ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>