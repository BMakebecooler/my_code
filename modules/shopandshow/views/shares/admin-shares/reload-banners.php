<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

/* @var $message string */
/* @var $this yii\web\View */

use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

?>

<?php $form = ActiveForm::begin(['enableClientValidation'=>false]); ?>

<? if ($message): ?>
    <? \yii\bootstrap\Alert::begin([
        'options' => [
            'class' => 'alert-success',
        ]
    ]); ?>
    <?= $message; ?>
    <? \yii\bootstrap\Alert::end(); ?>
<? endif; ?>

<?= Html::submitButton("Перезалить баннеры", [
    'class' => 'btn btn-primary',
    'onclick' => "return sx.CmsActiveFormButtons.go('apply');",
]); ?>

<?php ActiveForm::end(); ?>
