<?php

use kartik\select2\Select2;
use yii\web\JsExpression;
use skeeks\cms\modules\admin\components\UrlRule;

/* @var $this yii\web\View */
/* @var $model \modules\shopandshow\widgets\grid\Block6Widget */


?>
<?= $form->fieldSet(\Yii::t('app', 'Параметры')); ?>
<?= $form->field($model, 'header')->textInput(); ?>
<?= $form->field($model, 'subHeader')->textInput(); ?>
<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet('Баннер'); ?>
<?= $form->field($model, 'image_id_0')->widget(\modules\shopandshow\widgets\StorageImage::className()); ?>

<?= $form->field($model, 'imageUrl[0]')->textInput(); ?>
<?= $form->field($model, 'imageTitle[0]')->textInput(); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Служебные')); ?>
<?= $form->field($model, 'viewFile')->textInput(); ?>
<?= $form->fieldSetEnd(); ?>


