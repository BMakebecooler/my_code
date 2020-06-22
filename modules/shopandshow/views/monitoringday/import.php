<?php
use modules\shopandshow\models\monitoringday\PlanImport;
use yii\bootstrap\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $message string */
/* @var $this yii\web\View */
/* @var $model PlanImport */

?>

<div class="h3">Импорт плана на месяц</div>

<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => false,
    'options' => ['enctype'=>'multipart/form-data']
]); ?>

<? if ($message): ?>
    <? \yii\bootstrap\Alert::begin([
        'options' => [
            'class' => 'alert-success',
        ]
    ]); ?>
    <?= $message; ?>
    <? \yii\bootstrap\Alert::end(); ?>
<? endif; ?>

<?= $form->fieldSelect($model, 'type_plan', \modules\shopandshow\models\monitoringday\PlanDay::getTypeList()); ?>

<?= $form->field($model, 'period')->widget(
    \kartik\date\DatePicker::class,
    [
        'pluginOptions' => [
          'format' => 'yyyy-mm',
      ]
    ]
); ?>

<?= $form->field($model, 'file')->fileInput(['accept' => ".csv"]) ?>

<?= Html::submitButton("Загрузить файл", [
    'class' => 'btn btn-primary',
]); ?>


<?php ActiveForm::end(); ?>
