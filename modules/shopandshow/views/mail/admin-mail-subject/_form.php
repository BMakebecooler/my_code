<?php
/* @var $this yii\web\View */

/* @var $model MailSubject */

use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use modules\shopandshow\models\mail\MailTemplate;
use modules\shopandshow\models\mail\MailSubject;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use yii\helpers\ArrayHelper;

$MailTemplates = MailTemplate::find()
    //->select(['id', 'name', 'active'])
    ->orderBy('id')
    ->all();


$MailTemplates =
    ArrayHelper::map($MailTemplates, 'id', function (MailTemplate $MailTemplate) {
        return ($MailTemplate->active == 'Y' ? ' Y | ' : 'N | ') . ' -- ' . $MailTemplate->template . ' -- ' . $MailTemplate->name;
    });

$form = ActiveForm::begin(); ?>

<?= $form->field($model, 'name')->textInput(); ?>

<?= $form->fieldCheckboxBoolean($model, 'active'); ?>

<?= $form->field($model, "begin_datetime")->widget(
    DateControl::class,
    [
        'type' => DateControl::FORMAT_DATETIME,
        'displayFormat' => 'php:Y-m-d H:i'

    ]
); ?>

<?= $form->field($model, "end_datetime")->widget(
    DateControl::class,
    [
        'type' => DateControl::FORMAT_DATETIME,
        'displayFormat' => 'php:Y-m-d H:i',
    ]
); ?>

<?= $form->field($model, 'subject')->textInput(); ?>

<?= $form->field($model, 'template_id')->widget(Select2::classname(), [
    'options' => ['multiple' => false],
    'data' => $MailTemplates,
    'showToggleAll' => false,
    'pluginOptions' => [
        'maximumInputLength' => 10,
        'allowClear' => true,
    ],
]); ?>



<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
