<?php

use modules\shopandshow\models\monitoringday\PlanTables;
use yii\bootstrap\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model PlanTables */

$tables = [
    ['onair' => null, 'title' => 'Все товары'],
    ['onair' => true, 'title' => 'Товары в эфире'],
    ['onair' => false, 'title' => 'Товары НЕ в эфире'],
];
?>

<div class="h3">Продажи по рубрикам и ПЭ</div>
<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => false,
    'method' => 'GET',
    'action' => '/'.\Yii::$app->request->pathInfo,
    'usePjax' => false
]); ?>

<?= $form->field($model, 'date_from')->widget(
    \kartik\date\DatePicker::class,
    [
        'pluginOptions' => [
          'format' => 'yyyy-mm-dd',
      ]
    ]
); ?>

<?= $form->field($model, 'date_to')->widget(
    \kartik\date\DatePicker::class,
    [
        'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
        ]
    ]
); ?>

<?= $form->field($model, 'showCts')->checkbox(['id' => 'show_cts', 'value' => 1]); ?>
<?= $form->field($model, 'useOffset')->checkbox(['id' => 'useOffset', 'value' => 1]); ?>

<?= Html::submitButton("Показать", [
    'class' => 'btn btn-primary',
    'name' => 'submitType',
    'value' => 'show'
]); ?>

<?= Html::submitButton("Экспорт в Excel", [
    'class' => 'btn btn-primary',
    'name' => 'submitType',
    'value' => 'export',
]); ?>

<hr>

<?
$onairCategories = $model->getOnairCategories();
$treeCategories = $model->getCategories();
?>

<? if ($model->showCts && $model->isOneDay()): ?>
    <?
    $cts = \modules\shopandshow\lists\Shares::getCtsProduct($model->date_from);
    $ctsSales = \common\helpers\ArrayHelper::map($model->getBasketCtsSales(), 'order_date', 'product_price');
    ?>
    <h1>Товар Цтс</h1>
    <table class="table table-condenced table-bordered">
        <tr>
            <th>Рубрика</th>
            <? foreach ($onairCategories as $onairCategory): ?>
                <th><?= $onairCategory; ?></th>
            <? endforeach; ?>
            <th>Сумма</th>
        </tr>
        <tr>
            <td style="color: red;"><?= $cts ? $cts->product->name : '(цтс не найден)'; ?></td>
            <? foreach ($onairCategories as $onairTime => $onairCategory): ?>
                <td><?= \Yii::$app->formatter->asDecimal($ctsSales[$onairTime/1000] ?? 0); ?></td>
            <? endforeach; ?>
            <td><?= \Yii::$app->formatter->asDecimal($ctsSales ? array_sum($ctsSales) : 0); ?></td>
        </tr>
    </table>
    <br>
<? endif; ?>

<? foreach ($tables as $table): ?>
    <h1><?= $table['title']; ?></h1>
    <table class="table table-condenced table-bordered">
        <tr>
            <th>Рубрика</th>
            <? foreach ($onairCategories as $onairCategory): ?>
                <th><?= $onairCategory; ?></th>
            <? endforeach; ?>
            <th>Сумма</th>
            <th>Рубрика</th>
        </tr>

        <? $onAirSum = []; $deliverySum = []; ?>
        <? foreach ($treeCategories as $category): ?>
            <?
            $products = $model->getBasketProductSalesData($category->id, $table['onair']);
            ?>
            <tr>
                <td><?=$category->name; ?></td>
                <? foreach ($onairCategories as $onairTime => $onairCategory): ?>
                    <? $onAirSum[$onairTime] = ($onAirSum[$onairTime] ?? 0) + ($products[$onairTime/1000] ?? 0); ?>
                    <td><?= \Yii::$app->formatter->asDecimal($products[$onairTime/1000] ?? 0); ?></td>
                <? endforeach; ?>
                <td><?= \Yii::$app->formatter->asDecimal($products ? array_sum($products) : 0); ?></td>
                <td><?=$category->name; ?></td>
            </tr>
        <? endforeach; ?>

        <? if ($table['onair'] === null): ?>
        <tr>
            <td align="right"><b>Доставка</b></td>
            <? $deliverySum = $model->getOrdersDeliveryData(); ?>
            <? foreach ($onairCategories as $onairTime => $onairCategory): ?>
                <td><?= \Yii::$app->formatter->asDecimal($deliverySum[$onairTime/1000] ?? 0); ?></td>
            <? endforeach; ?>
            <td><?= \Yii::$app->formatter->asDecimal($deliverySum ? array_sum($deliverySum) : 0); ?></td>
            <td><b>Доставка</b></td>
        </tr>
        <? endif; ?>

        <tr>
            <td align="right"><b>Сумма</b></td>
            <? foreach ($onairCategories as $onairTime => $onairCategory): ?>
                <td><?= \Yii::$app->formatter->asDecimal($onAirSum[$onairTime] /*+ ($deliverySum[$onairTime/1000] ?? 0)*/); ?></td>
            <? endforeach; ?>
            <td><?= \Yii::$app->formatter->asDecimal($onAirSum ? array_sum($onAirSum) : 0 /*+ $deliverySum ? array_sum($deliverySum) : 0*/); ?></td>
            <td></td>
        </tr>
    </table>
    <br>
<? endforeach; ?>

<?php ActiveForm::end(); ?>



