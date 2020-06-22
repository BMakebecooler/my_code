<?php

/* @var $model GtMetrix */


use miloschuman\highcharts\Highcharts;
use modules\shopandshow\models\services\GtMetrix;
use skeeks\cms\base\widgets\ActiveForm;
use yii\helpers\Html;

?>

<div class="h3">Мониторинг загрузки сайта по Gt-metrix</div>

<?php $form = ActiveForm::begin([
    'action' => "/" . \Yii::$app->request->getPathInfo(),
    'enableAjaxValidation' => false,
    'method' => 'get',
]); ?>

<?= $form->field($model, 'month')->widget(
    \kartik\date\DatePicker::class,
    [
        'pluginOptions' => [
            'autoclose' => true,
            'startView' => 'year',
            'minViewMode' => 'months',
            'format' => 'yyyy-mm'
        ]
    ]
); ?>

<?= Html::submitButton("Показать", [
    'class' => 'btn btn-primary',
]); ?>

    <hr>

<?= Highcharts::widget(array_merge(
        $model->getHighchartsData(),
        ['scripts' => ['modules/drilldown']]
    )
); ?>

<?php ActiveForm::end(); ?>