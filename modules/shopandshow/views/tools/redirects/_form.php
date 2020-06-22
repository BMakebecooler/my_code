<?php

use \yii\widgets\ActiveForm as ActiveForm;

/* @var $this yii\web\View */
/* @var $model \modules\shopandshow\models\tools\Redirect */
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

<?= $form->field($model, 'file')->fileInput() ?>

<button>Загрузить</button>

<?php ActiveForm::end(); ?>