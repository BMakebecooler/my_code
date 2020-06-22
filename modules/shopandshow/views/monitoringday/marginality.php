<?php

use yii\bootstrap\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model \modules\shopandshow\models\monitoringday\Marginality */

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

<div class="h3">Маржинальность за <?= $model->date ?></div>

<hr>

<?
$data = $model->getData();
?>

<div class="container">
    <table class="table table-bordered table-hover">
        <thead>
        <tr>
            <th></th>
            <th>Всего</th>
            <th>Эфирные лоты</th>
            <th>Не эфирные лоты</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Сумма</td>
            <td><?= $data['sum_all'] ?></td>
            <td><?= $data['sum_not_efir'] ?></td>
            <td><?= $data['sum_efir'] ?></td>
        </tr>
        <tr>
            <td>Корзина</td>
            <td><?= $data['basket_all'] ?></td>
            <td><?= $data['basket_not_efir'] ?></td>
            <td><?= $data['basket_efir'] ?></td>
        </tr>
        <tr>
            <td>Телефон</td>
            <td><?= $data['phone6010_all'] ?></td>
            <td><?= $data['phone6010_not_efir'] ?></td>
            <td><?= $data['phone6010_efir'] ?></td>
        </tr>
        <tr>
            <td>Брошенные кррзины</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
        </tr>
        </tbody>
    </table>

    <table class="table table-bordered table-hover">
        <tbody>
        <tr>
            <td>Маржа (руб)</td>
            <td><?= $data['marga_all'] ?></td>
            <td><?= $data['marga_not_efir'] ?></td>
            <td><?= $data['marga_efir'] ?></td>
        </tr>
        <tr>
            <td>Маржинальность</td>
            <td><?= $data['marginality_all'] ?></td>
            <td><?= $data['marginality_not_efir'] ?></td>
            <td><?= $data['marginality_efir'] ?></td>
        </tr>

        </tbody>
    </table>
</div>

<style type="text/css">
    .container table td {
        width: 20%;
    }
</style>

<?php ActiveForm::end(); ?>
