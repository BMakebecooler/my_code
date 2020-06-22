<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

/* @var $message string */
/* @var $this yii\web\View */
/* @var $mail_id integer */

use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use yii\helpers\Html;

$model = $this->context->model;

$getActiveSubjectUrl = UrlHelper::construct(['mail/admin-mail-template/get-subject'])->enableAdmin()
    ->normalizeCurrentRoute()
    ->toString();

$this->registerJs(<<<JS
ActiveBannerDateControlChange = function (){
    var templateId = {$model->id};
    var el = jQuery('input[name="MailTemplate[begin_date]"]');
    var url = "{$getActiveSubjectUrl}";
    jQuery.post( url, { 
        templateId : templateId, 
        begin_date : el.val()
    }, function( data ) {
        if(data){
            jQuery('input[name="MailTemplate[ActiveSubject]"]').val(data);
        }
    }); 
    return false;
}
JS
);

?>

<?php $form = ActiveForm::begin(['enableAjaxValidation'=>false]); ?>

    <? if ($message): ?>
        <? \yii\bootstrap\Alert::begin([
            'options' => [
                'class' => 'alert-success',
            ]
        ]); ?>
        <?= $message; ?>
        <? \yii\bootstrap\Alert::end(); ?>
    <? endif; ?>

    <?= $form->field($model, 'ActiveSubject')->textInput(); ?>
    <?= $form->field($model, 'from')->textInput(); ?>
    <?= $form->field($model, 'mail_to')->textInput()->label('Кому'); ?>
    <?= $form->field($model, 'begin_date')->widget(
        \kartik\datecontrol\DateControl::class,
        [
            'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
            'displayFormat' => 'php:Y-m-d H:i',
            'pluginEvents' => [
                'change' => 'ActiveBannerDateControlChange',
            ],
        ]
    )->label('Дата активности баннеров'); ?>

<?= Html::submitButton('Предпросмотр', [
    'name' => 'action',
    'value' => 'show',
    'class' => 'btn btn-default',
]); ?>

<?= Html::submitButton('Test - MailGun', [
    'name' => 'action',
    'value' => 'send',
    'class' => 'btn btn-primary',
    'style' => 'margin-left: 10px',
]); ?>

<?= Html::submitButton('Final Test - GetResponse', [
    'name' => 'action',
    'value' => 'send-getresponse',
    'class' => 'btn btn-danger',
    'style' => 'margin-left: 10px',
]); ?>
<br><br>
<div class="alert alert-warning">
    <p>Test – MailGun: предварительная проверка через "сервисный" почтовик - адрес указывается в поле "Кому".</p>
    <p>Final Test – GetResponse: финальная проверка, письмо отправляется по списку адресов из кампании final_test в Getresponse.
       Использовать только после успешного прохождения обычного теста.</p>
</div>

<?php
if(isset($mail_id)) {
    try {
        $mailPreviewUrl = UrlHelper::construct(['mail/admin-mail-dispatch/show'])->enableAdmin()
            ->addData([$this->context->requestPkParamName => $mail_id])->normalizeCurrentRoute()->toString();
        echo '<br><br><iframe width="100%" height="800px" src="'.$mailPreviewUrl.'"></iframe>';
    }
    catch(\Exception $e) {
        echo $e->getMessage();
    }

}
?>

<?php ActiveForm::end(); ?>
