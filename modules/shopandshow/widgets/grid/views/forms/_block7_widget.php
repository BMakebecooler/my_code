<?php

use kartik\select2\Select2;
use yii\web\JsExpression;
use skeeks\cms\modules\admin\components\UrlRule;

/* @var $this yii\web\View */
/* @var $model \modules\shopandshow\widgets\grid\Block7Widget */


?>
<?= $form->fieldSet(\Yii::t('app', 'Параметры')); ?>
<?= $form->field($model, 'header')->textInput(); ?>
<?= $form->field($model, 'subHeader')->textInput(); ?>
<?= $form->fieldSetEnd(); ?>

<?php for ($i = 0; $i < 3; $i++): ?>
    <?= $form->fieldSet('Баннер ' . ($i + 1)); ?>
    <?= $form->field($model, 'image_id_' . $i)->widget(\modules\shopandshow\widgets\StorageImage::className()); ?>

    <?= $form->field($model, 'imageUrl[' . $i . ']')->textInput(); ?>
    <?= $form->field($model, 'imageTitle[' . $i . ']')->textInput(); ?>

    <?= $form->fieldSetEnd(); ?>
<?php endfor; ?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Служебные')); ?>
<?= $form->field($model, 'viewFile')->textInput(); ?>
<?= $form->fieldSetEnd(); ?>


