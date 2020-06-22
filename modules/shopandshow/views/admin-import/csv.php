<?php
use modules\shopandshow\models\import\NewyearUploadForm;
use yii\bootstrap\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $message string */
/* @var $this yii\web\View */
/* @var $model NewyearUploadForm */

?>

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

<?= $form->fieldSelect($model, 'tree_id', \common\helpers\ArrayHelper::map($categories, 'id', 'name')); ?>

<?= $form->field($model, 'file')->fileInput(['accept' => ".csv"]) ?>

<?= Html::submitButton("Загрузить файл", [
    'class' => 'btn btn-primary',
    'onclick' => "return sx.CmsActiveFormButtons.go('apply');",
]); ?>


<?php ActiveForm::end(); ?>
