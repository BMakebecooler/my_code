<?php

use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;


/* @var $this yii\web\View */
/* @var $model \modules\shopandshow\models\shares\SsShare */
?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Main')); ?>

<?= $form->field($model, 'code')->textInput(); ?>
<?= $form->field($model, 'description')->textInput(); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
