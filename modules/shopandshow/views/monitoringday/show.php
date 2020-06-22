<?php

use modules\shopandshow\models\monitoringday\Plan;
use modules\shopandshow\models\shop\ShopOrder;
use yii\bootstrap\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model Plan */

?>

<div class="h3">Мониторинг продаж с сайта</div>

<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => false,
]); ?>

<?= $form->field($model, 'date')->widget(
    \kartik\date\DatePicker::class,
    [
        'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
        ]
    ]
); ?>

<?= Html::submitButton("Показать", [
    'class' => 'btn btn-primary',
]); ?>

<hr>

<?php
$dataProvider = $model->getDataProvider();
$sumPlan = $model->getSumPlan($dataProvider);
$sumFact = $model->getSumFact($dataProvider);
$totalSum = $model->getTotalSum();
$totalSumAcc = $model->getTotalSumAccFromTotalSum($totalSum);

$ordersSummary = $model->getOrdersSummary();
$ordersSummarySite = $model->getOrdersSummarySum($ordersSummary, ShopOrder::SOURCE_SITE);
$ordersSummaryPhone1 = $model->getOrdersSummarySum($ordersSummary, ShopOrder::SOURCE_KFSS, ShopOrder::SOURCE_DETAIL_PHONE1);
$ordersSummaryPhone2 = $model->getOrdersSummarySum($ordersSummary, ShopOrder::SOURCE_KFSS, ShopOrder::SOURCE_DETAIL_PHONE2);
$marginSummary = $model->getMarginSummary();


$deviationPercentage = $sumPlan > 0 ? 100 * ($sumFact - $sumPlan) / $sumPlan : 100 * ($sumFact - $sumPlan);

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
?>

<?= \miloschuman\highcharts\Highcharts::widget($model->getHighchartsData($dataProvider)); ?>

<div class="h4">Сводная информация за день</div>
<div><b>Продажи сайта: </b><?= \Yii::$app->formatter->asDecimal($ordersSummarySite); ?> руб.</div>
<div><b>Продажи с телефона 8 800 7752250: </b><?= \Yii::$app->formatter->asDecimal($ordersSummaryPhone1); ?> руб.</div>
<div><b>Продажи с телефона 8 800 3016010: </b><?= \Yii::$app->formatter->asDecimal($ordersSummaryPhone2); ?> руб.</div>
<div><b>Маржа: </b><?= \Yii::$app->formatter->asDecimal(round($marginSummary)); ?> руб.</div>

<div class="h4">План/факт по часу</div>
<?= \skeeks\cms\modules\admin\widgets\GridView::widget([
    'id' => 'plan-table',
    'dataProvider' => $dataProvider,
    'showFooter' => true,
    'footerRowOptions' => ['style' => 'font-weight:bold;'],
    'layout' => '{items}',
    'columns' => [
        [
            'label' => "Время",
            'value' => function ($row) {
                return sprintf('%02d:00 - %02d:00', $row['hour'], $row['hour'] + 1);
            },
            'contentOptions' => function ($model, $key, $index, $column) {
                return ['style' => 'white-space: nowrap'];
            },
        ],
        [
            'label' => "Категория эфира",
            'attribute' => 'category',
            'footer' => 'Итого'
        ],
        [
            'label' => "План",
            'value' => function ($row) {
                return \Yii::$app->formatter->asDecimal(round($row['sum_plan']));
            },
            'footer' => \Yii::$app->formatter->asDecimal($sumPlan, 2)
        ],
        [
            'label' => "Факт",
            'value' => function ($row) {
                return \Yii::$app->formatter->asDecimal(round($row['sum_fact']));
            },
            'footer' => \Yii::$app->formatter->asDecimal($sumFact, 2)
        ],
        [
            'label' => "% отклонения",
            'format' => 'raw',
            'value' => function ($row) {
                if (empty($row['sum_fact'])) {
                    return 0;
                }

                $value = 100*($row['sum_fact'] - $row['sum_plan']) / $row['sum_plan'];

                return '<span style="color: '.($value < 0 ? 'red' : 'green').'">'.
                    \Yii::$app->formatter->asDecimal($value, 2).
                    '%</span>';
            },
            'footer' => \Yii::$app->formatter->asDecimal($deviationPercentage, 2).'%'
        ],
        [
            'label' => 'Число заказов',
            'attribute' => 'amount',
            'footer' => array_sum(array_column($dataProvider->getModels(), 'amount'))
        ],
        [
            'label' => "Продажи эфира",
            'format' => 'raw',
            'value' => function ($row) use ($totalSum, $model) {
                $key = sprintf('%s %02d:00:00', $model->date, $row['hour']);
                if (!isset($totalSum[$key])) return 0;

                return \Yii::$app->formatter->asDecimal($totalSum[$key]);
            },
            'footer' => \Yii::$app->formatter->asDecimal(array_sum($totalSum), 2)
        ],
        [
            'label' => "Доля продаж с сайта",
            'format' => 'raw',
            'value' => function ($row) use ($totalSum, $model) {
                if (empty($row['sum_fact'])) return 0;

                $key = sprintf('%s %02d:00:00', $model->date, $row['hour']);

                if (!isset($totalSum[$key])) return 0;

                $value = 100*($row['sum_fact']) / $totalSum[$key];

                return '<span style="color: '.($value < 7 ? 'red' : 'green').'">'.
                    \Yii::$app->formatter->asDecimal($value, 2).
                    '%</span>';
            },
            'footer' => ($totalSum ? \Yii::$app->formatter->asDecimal(100 * $sumFact / array_sum($totalSum), 2) : 0).'%'
        ],
        [
            'label' => "План накопленный",
            'value' => function ($row) {
                return \Yii::$app->formatter->asDecimal(round($row['sum_plan_acc']));
            },
            'footer' => \Yii::$app->formatter->asDecimal($sumPlan)
        ],
        [
            'label' => "Факт накопленный",
            'value' => function ($row) {
                return \Yii::$app->formatter->asDecimal(round($row['sum_fact_acc']));
            },
            'footer' => \Yii::$app->formatter->asDecimal($sumFact)
        ],
        [
            'label' => "% отклонения",
            'format' => 'raw',
            'value' => function ($row) {
                if (empty($row['sum_fact_acc'])) {
                    return 0;
                }

                $value = 100*($row['sum_fact_acc'] - $row['sum_plan_acc']) / $row['sum_plan_acc'];

                return '<span style="color: '.($value < 0 ? 'red' : 'green').'">'.
                    \Yii::$app->formatter->asDecimal($value, 2).
                    '%</span>';
            },
            'footer' => \Yii::$app->formatter->asDecimal($deviationPercentage, 2).'%'
        ],
        [
            'label' => "Продажи эфира накопленные",
            'format' => 'raw',
            'value' => function ($row) use ($totalSumAcc, $model) {
                $key = sprintf('%s %02d:00:00', $model->date, $row['hour']);
                if (!isset($totalSumAcc[$key])) return 0;

                return \Yii::$app->formatter->asDecimal($totalSumAcc[$key]);
            },
            'footer' => \Yii::$app->formatter->asDecimal(array_sum($totalSum), 0)
        ],
        [
            'label' => "Доля продаж с сайта накопл.",
            'format' => 'raw',
            'value' => function ($row) use ($totalSumAcc, $model) {
                if (empty($row['sum_fact_acc'])) return 0;

                $key = sprintf('%s %02d:00:00', $model->date, $row['hour']);

                if (!isset($totalSumAcc[$key])) return 0;

                $value = 100*($row['sum_fact_acc']) / $totalSumAcc[$key];

                return '<span style="color: '.($value < 7 ? 'red' : 'green').'">'.
                    \Yii::$app->formatter->asDecimal($value, 2).
                    '%</span>';
            },
            'footer' => ($totalSumAcc ? \Yii::$app->formatter->asDecimal(100 * $sumFact / array_sum($totalSum), 2) : 0).'%'
        ],

    ],

]);
?>

<?php
$caregoriesDataProvider = $model->getCategoryDataProvider();
$columns = $model->getCategoryColumns($caregoriesDataProvider);
?>

<div class="h4">План/факт по категориям</div>
<?= \skeeks\cms\modules\admin\widgets\GridView::widget([
    'id' => 'plan-table-category',
    'dataProvider' => $caregoriesDataProvider,
    'layout' => '{items}',
    'showFooter' => true,
    'footerRowOptions' => ['style' => 'font-weight:bold;'],
    'columns' => $columns
]);

?>

<?php ActiveForm::end(); ?>

<a target="_blank" href="/~sx/shopandshow/monitoringday/plan/marginality">Отчет по маржинальности</a>
