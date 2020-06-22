<?php

use kartik\select2\Select2;
use yii\web\JsExpression;
use skeeks\cms\modules\admin\components\UrlRule;

/* @var $this yii\web\View */
/* @var $model \modules\shopandshow\widgets\grid\DefaultWidget */



?>
<?= $form->fieldSet(\Yii::t('app', 'Параметры')); ?>
<?= $form->field($model, 'timer')->checkbox(); ?>
<?= $form->field($model, 'header')->textInput(); ?>

<?= $form->field($model, 'image_id')->widget(\modules\shopandshow\widgets\StorageImage::className()); ?>

<?= $form->field($model, 'imageUrl')->textInput(); ?>
<?= $form->field($model, 'imageTitle')->textInput(); ?>

<?= $form->field($model, 'description')->textInput(); ?>
<?= $form->field($model, 'descriptionColored')->textInput(); ?>
<?= $form->field($model, 'descriptionColor')->textInput(); ?>

<?= $form->field($model, 'button')->checkbox(); ?>
<?= $form->field($model, 'buttonUrl')->textInput(); ?>
<?= $form->field($model, 'buttonTitle')->textInput(); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Служебные')); ?>
<?= $form->field($model, 'viewFile')->textInput(); ?>
<?= $form->fieldSetEnd(); ?>


