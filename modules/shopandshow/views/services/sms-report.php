<?php

/* @var $model \modules\shopandshow\models\services\Sms */


use miloschuman\highcharts\Highcharts;
use skeeks\cms\base\widgets\ActiveForm;
use yii\helpers\Html;

?>

    <div class="h3">Мониторинг отправки смс</div>

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

<?= Highcharts::widget($model->getHighchartsData()); ?>

<?php ActiveForm::end(); ?>