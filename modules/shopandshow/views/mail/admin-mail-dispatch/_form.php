<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use skeeks\cms\widgets\formInputs\comboText\ComboTextInputWidget;
use yii\helpers\ArrayHelper;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

$mailTemplateList = ArrayHelper::map(\modules\shopandshow\models\mail\MailTemplate::find()->all(), 'id', 'name');

/* @var $this yii\web\View */
?>

<?php $form = ActiveForm::begin(); ?>

    <?= $form->fieldSelect($model, 'mail_template_id', $mailTemplateList); ?>
    <?= $form->field($model, 'from')->textInput(); ?>
    <?= $form->field($model, 'to')->textInput(); ?>
    <?= $form->field($model, 'subject')->textInput(); ?>
    <?= $form->field($model, 'body')->widget(
        ComboTextInputWidget::className(), ['defaultEditor' => ComboTextInputWidget::CONTROLL_EDITOR]
    ); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
