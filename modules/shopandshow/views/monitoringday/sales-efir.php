<?php

use modules\shopandshow\models\monitoringday\SalesEfir;
use yii\bootstrap\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model SalesEfir */

$tables = [
    ['onair' => true, 'title' => 'Товары в эфире'],
    ['onair' => false, 'title' => 'Товары НЕ в эфире'],
    ['onair' => null, 'title' => 'Остальные товары'],
];
?>

<div class="h3">Продажи ПЭЧ</div>
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

<?= $form->field($model, 'showCts')->checkbox(['id' => 'show_cts', 'value' => 1]); ?>

<?= Html::submitButton("Показать", [
    'class' => 'btn btn-primary',
    'name' => 'submitType',
    'value' => 'show'
]); ?>

<hr>

<?
$onairCategories = $model->getOnairCategories();
$treeCategories = $model->getCategories();
?>

<? if ($model->showCts): ?>
    <?
    $cts = \modules\shopandshow\lists\Shares::getCtsProduct($model->date);
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

        <? $onAirSum = []; ?>
        <? foreach ($treeCategories as $category): ?>
            <?
            $products = $model->getBasketProductSales($category->id, $table['onair']);
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

        <tr>
            <td align="right"><b>Сумма</b></td>
            <? foreach ($onairCategories as $onairTime => $onairCategory): ?>
                <td><?= \Yii::$app->formatter->asDecimal($onAirSum[$onairTime]); ?></td>
            <? endforeach; ?>
            <td><?= \Yii::$app->formatter->asDecimal($onAirSum ? array_sum($onAirSum) : 0); ?></td>
            <td></td>
        </tr>
    </table>
    <br>
<? endforeach; ?>

<? $onairCategories = $model->getOnairCategories(false); ?>

<h1>Продажи лотов каждого часа эфира</h1>
<table class="table table-condenced table-bordered">
    <tr>
        <th>Рубрика</th>
        <? foreach ($onairCategories as $onairCategory): ?>
            <th><?= $onairCategory; ?></th>
        <? endforeach; ?>
        <th>Сумма</th>
        <th>Рубрика</th>
    </tr>

    <?
    $onAirProducts = $model->getOnairProducts();
    $onAirSum = [];
    ?>

    <? foreach ($onairCategories as $onairTime => $category): ?>
        <?
        $onAirSales =$model->getOnairSales($onAirProducts[$onairTime/1000] ?? []);
        ?>
        <tr>
            <th><?=$category; ?></th>
            <? foreach ($onairCategories as $onairSaleTime => $onairCategory): ?>
                <?
                $onAirSale = $onAirSales[$onairSaleTime/1000] ?? [];
                $onAirSum[$onairSaleTime] = ($onAirSum[$onairSaleTime] ?? 0) + ($onAirSale['product_price'] ?? 0);
                ?>
                <td><?= \Yii::$app->formatter->asDecimal($onAirSale['product_price'] ?? 0); ?></td>
            <? endforeach; ?>
            <td><?= \Yii::$app->formatter->asDecimal($onAirSales ? array_sum(array_column($onAirSales, 'product_price')) : 0); ?></td>
            <th><?=$category; ?></th>
        </tr>
    <? endforeach; ?>

    <tr>
        <td align="right"><b>Сумма</b></td>
        <? foreach ($onairCategories as $onairTime => $category): ?>
            <td><?= \Yii::$app->formatter->asDecimal($onAirSum[$onairTime]); ?></td>
        <? endforeach; ?>
        <td><?= \Yii::$app->formatter->asDecimal($onAirSum ? array_sum($onAirSum) : 0); ?></td>
        <td></td>
    </tr>

</table>
<br>

<?php ActiveForm::end(); ?>



