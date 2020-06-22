<?php

use kartik\datecontrol\DateControl;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model \modules\shopandshow\models\shop\stock\SegmentFile */
?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'file')->widget(
    \skeeks\cms\widgets\formInputs\ModelStorageFiles::className()
); ?>

<?= $form->field($model, 'name'); ?>

<?= $form->field($model, 'begin_datetime')->widget(DateControl::classname(), [
    'displayFormat' => 'php:Y-m-d H:i',
    'type' => DateControl::FORMAT_DATETIME,
]); ?>

<?= $form->field($model, 'end_datetime')->widget(DateControl::classname(), [
    'displayFormat' => 'php:Y-m-d H:i',
    'type' => DateControl::FORMAT_DATETIME,
]); ?>

<?= $form->buttonsStandart($model) ?>

<?php ActiveForm::end(); ?>
