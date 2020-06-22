<?php

use miloschuman\highcharts\Highcharts;
use modules\shopandshow\models\shop\stock\forms\SalesStock;
use yii\bootstrap\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model SalesStock */

?>

<div class="h3">Продажи стока</div>
<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => false,
    'method' => 'POST',
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
    'name' => 'submitType',
    'value' => 'show'
]); ?>

<?php
$treeCategories = $model->getCategories();
$stockSales = $model->getBasketStockSales();
$stockSalesForChart = $model->prepareForHighCharts($stockSales);

$totals = $model->getTotals();
?>

<hr>

<div class="well">
    Всего продаж: <?= \Yii::$app->formatter->asDecimal($totals['total']); ?><br>
    Сток: <?= \Yii::$app->formatter->asDecimal($totals['stock']); ?><br>
    Доля стока: <?= \Yii::$app->formatter->asPercent($totals['stock'] / ($totals['total'] ?: 1)); ?><br>
    <i>только товары, без учеты стоимости доставки</i>
</div>

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
?>
<?= Highcharts::widget($model->getHighchartsData($stockSalesForChart)); ?>

<?
$stockSumPrice = [];
$stockSumQuantity = [];
?>
<h1>Продажи по типу стока</h1>
<table class="table table-condenced table-bordered">
    <tr>
        <th>Период/Тип стока</th>
        <? foreach ($model->getStockTypes() as $stockType): ?>
            <th><?= $stockType; ?></th>
        <? endforeach; ?>
        <th>Сумма</th>
        <th>Кол-во</th>
    </tr>

    <?php foreach ($stockSales as $saleDate => $saleSegments): ?>
        <tr>
            <td><?= date('H:i', $saleDate); ?> - <?= date('H:i', $saleDate + HOUR_1); ?></td>
            <? foreach ($model->getStockTypes() as $stockType): ?>
                <? $stockSumPrice[$stockType] = ($stockSumPrice[$stockType] ?? 0) + ($saleSegments[$stockType]['price'] ?? 0); ?>
                <? $stockSumQuantity[$stockType] = ($stockSumQuantity[$stockType] ?? 0) + ($saleSegments[$stockType]['quantity'] ?? 0); ?>
                <td><?= \Yii::$app->formatter->asDecimal($saleSegments[$stockType]['price'] ?? 0); ?></td>
            <? endforeach; ?>
            <td><?= \Yii::$app->formatter->asDecimal($saleSegments ? array_sum(array_column($saleSegments, 'price')) : 0); ?></td>
            <td><?= \Yii::$app->formatter->asDecimal($saleSegments ? array_sum(array_column($saleSegments, 'quantity')) : 0); ?></td>
        </tr>
    <?php endforeach; ?>

    <tr>
        <td align="right"><b>Сумма</b></td>
        <? foreach ($model->getStockTypes() as $stockType): ?>
            <td><?= \Yii::$app->formatter->asDecimal($stockSumPrice[$stockType]); ?></td>
        <? endforeach; ?>
        <td><?= \Yii::$app->formatter->asDecimal($stockSumPrice ? array_sum($stockSumPrice) : 0); ?></td>
        <td><?= \Yii::$app->formatter->asDecimal($stockSumQuantity ? array_sum($stockSumQuantity) : 0); ?></td>
    </tr>
</table>
<br>

<?
$stockSumPrice = [];
$categoryStockSales = [];
?>
<h1>Продажи по категориям</h1>
<table class="table table-condenced table-bordered">
    <tr>
        <th>Период/Категория</th>
        <? foreach ($treeCategories as $category): ?>
            <th><?= $category->name; ?></th>
            <?
            $categoryStockSales[$category->id] = $model->getBasketStockSales($category->id);
            ?>
        <? endforeach; ?>
        <th>Сумма</th>
    </tr>

    <?php foreach ($stockSales as $saleDate => $foo): ?>
        <?php
        $segmentSum = 0;
        ?>
        <tr>
            <td><?= date('H:i', $saleDate); ?> - <?= date('H:i', $saleDate + HOUR_1); ?></td>
            <? foreach ($treeCategories as $category): ?>
                <?
                $categorySales = $categoryStockSales[$category->id];
                $sales = $categorySales[$saleDate] ?? [];
                $salesSum = array_sum(array_column($sales, 'price'));
                $segmentSum += $salesSum;
                $stockSumPrice[$category->id] = ($stockSumPrice[$category->id] ?? 0) + ($salesSum ?: 0);
                ?>
                <td><?= \Yii::$app->formatter->asDecimal($salesSum); ?></td>
            <? endforeach; ?>
            <td><?= \Yii::$app->formatter->asDecimal($segmentSum); ?></td>
        </tr>
    <?php endforeach; ?>

    <tr>
        <td align="right"><b>Сумма</b></td>
        <? foreach ($treeCategories as $category): ?>
            <td><?= \Yii::$app->formatter->asDecimal($stockSumPrice[$category->id]); ?></td>
        <? endforeach; ?>
        <td><?= \Yii::$app->formatter->asDecimal($stockSumPrice ? array_sum($stockSumPrice) : 0); ?></td>
    </tr>
</table>

<?php ActiveForm::end(); ?>



