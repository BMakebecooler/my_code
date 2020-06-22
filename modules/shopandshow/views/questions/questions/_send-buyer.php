<?php

use common\models\cmsContent\ContentElementFaq;
use modules\shopandshow\models\questions\QuestionEmail;
use \yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $message string */
/* @var $model ContentElementFaq */

$mails = QuestionEmail::findForBuyer($model)->all();
?>

<?php $form = ActiveForm::begin(); ?>

    <? if ($message): ?>
        <? \yii\bootstrap\Alert::begin([
            'options' => [
                'class' => 'alert-success',
            ]
        ]); ?>
        <?= $message; ?>
        <? \yii\bootstrap\Alert::end(); ?>
    <? endif; ?>

    <div class="container">
        <h2>Отправка письма баерам</h2>
        <? foreach ($mails as $mail): ?>
            <div><?= $mail->fio; ?> &lt;<?= $mail->email; ?>&gt;</div>
        <? endforeach; ?>
        <br>
        <?= Html::submitButton('Отправить', ['name' => 'send', 'class' => 'btn btn-primary']); ?>
        <br><br>
    </div>
<?php ActiveForm::end(); ?>
