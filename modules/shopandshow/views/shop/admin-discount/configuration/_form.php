<?php

use modules\shopandshow\models\shop\shopdiscount\Entity;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model modules\shopandshow\models\shop\shopdiscount\Configuration */

$entityList = Entity::find()->all();
?>

<div class="configuration-create-form">

    <?= $form->field($model, 'entity_id')->dropDownList($entityList) ?>

    <?= $form->field($model, 'params')->textInput(['maxlength' => true]) ?>

</div>
