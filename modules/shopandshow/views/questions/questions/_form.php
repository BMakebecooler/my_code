<?php
use common\helpers\User;
use common\models\cmsContent\ContentElementFaq;
use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model \common\models\cmsContent\ContentElementFaq */

if (User::can(ContentElementFaq::PERM_EDIT)) {
    $model->editor_lastview_at = time();
    $model->save(false, ['editor_lastview_at']);
}

if (!$model->published_at && (User::can(ContentElementFaq::PERM_EDIT) || User::can(ContentElementFaq::PERM_COPYRIGHT))) {
    $model->published_at = time();
}

?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->fieldSet('Редактировать'); ?>

<?= $form->field($model, 'created_at')->widget(
    \kartik\datecontrol\DateControl::class,
    [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        'displayFormat' => 'php:Y-m-d H:i:s',
        'disabled' => !User::isDeveloper()
    ]
); ?>
<?= $form->field($model, 'updated_at')->widget(
    \kartik\datecontrol\DateControl::class,
    [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        'displayFormat' => 'php:Y-m-d H:i:s',
        'disabled' => !User::isDeveloper()
    ]
); ?>
<?= $form->field($model, 'published_at')->widget(
    \kartik\datecontrol\DateControl::class,
    [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        'displayFormat' => 'php:Y-m-d H:i:s',
        'disabled' => !User::can(ContentElementFaq::PERM_EDIT) && !User::can(ContentElementFaq::PERM_COPYRIGHT)
    ]
); ?>

<div class="form-group field-contentelementfaq-createdby">
    <label class="control-label" for="contentelementfaq-createdby">Имя клиента</label>
    <input type="text" id="contentelementfaq-createdby" readonly class="form-control"
           value="<?= $model->createdBy->displayName; ?> <?= $model->createdBy->relatedPropertiesModel->getSmartAttribute('LAST_NAME'); ?>">
</div>

<div class="form-group field-contentelementfaq-useremail">
    <label class="control-label" for="contentelementfaq-useremail">Email клиента</label>
    <input type="text" id="contentelementfaq-useremail" readonly class="form-control"
           value="<?= $model->createdBy->email; ?>">
</div>

<div class="form-group field-contentelementfaq-userphone">
    <label class="control-label" for="contentelementfaq-userphone">Телефон клиента</label>
    <input type="text" id="contentelementfaq-userphone" readonly class="form-control"
           value="<?= $model->createdBy->phone; ?>">
</div>

<?= $form->field($model, 'username')->textInput(User::can(ContentElementFaq::PERM_EDIT) ? [] : ['readonly' => true])->hint('test'); ?>
<?= $form->field($model, 'email')->textInput(User::can(ContentElementFaq::PERM_EDIT) ? [] : ['readonly' => true]); ?>
<hr>
<div class="form-group field-contentelementfaq-product">
    <label class="control-label" for="contentelementfaq-product">Лот</label><br>
    <?= Html::a($model->element->getLotName(), $model->element->url, ['target' => '_blank', 'data-pjax' => 0]); ?>
</div>

<?php if (User::can(ContentElementFaq::PERM_EDIT)): ?>
    <div class="form-group">
        <label class="control-label">Отправка писем</label><br>
        <?= Html::button('Отправить в сервис', ['class' => 'btn btn-danger', 'id' => 'sendServiceBtn']); ?>
        <?= Html::button('Отправить баерам', ['class' => 'btn btn-primary', 'id' => 'sendBuyerBtn']); ?>
        <div class="hint-block">
            <?php if (!empty($model->sent_buyer_at)): ?>
                <b>Отправлено баерам: </b> <?= \Yii::$app->formatter->asDatetime($model->sent_buyer_at); ?><br>
            <?php endif; ?>
            <?php if (!empty($model->sent_service_at)): ?>
                <b>Отправлено в сервис: </b> <?= \Yii::$app->formatter->asDatetime($model->sent_service_at); ?><br>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?= $form->field($model, 'question')->textarea(User::can(ContentElementFaq::PERM_EDIT) ? [] : ['readonly' => true]); ?>
<?= $form->field($model, 'answer')->textarea(User::can(ContentElementFaq::PERM_EDIT) ? [] : ['readonly' => true]); ?>
<?= $form->field($model, 'buyer_answer')->textarea(User::can(ContentElementFaq::PERM_BUYER) ? [] : ['readonly' => true]); ?>
<?= $form->field($model, 'service_answer')->textarea(User::can(ContentElementFaq::PERM_SERVICE) ? [] : ['readonly' => true]); ?>
<?= $form->field($model, 'copyright_answer')->textarea(User::can(ContentElementFaq::PERM_COPYRIGHT) ? [] : ['readonly' => true]); ?>

<?= $form->field($model, 'status')->dropDownList(
    \common\models\cmsContent\ContentElementFaq::getStatusList(),
    (User::can(ContentElementFaq::PERM_SERVICE) || User::can(ContentElementFaq::PERM_EDIT)) ? [] : ['disabled' => true]
); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>

<?php

$sendBuyerUrl = \skeeks\cms\helpers\UrlHelper::construct(['questions/questions/send-buyer'])
    ->enableAdmin()
    ->addData(['id' => $model->id])
    ->normalizeCurrentRoute()->toString();

$sendServiceUrl = \skeeks\cms\helpers\UrlHelper::construct(['questions/questions/send-service'])
    ->enableAdmin()
    ->addData(['id' => $model->id])
    ->normalizeCurrentRoute()->toString();

?>
<?php if (User::can(ContentElementFaq::PERM_EDIT)) $this->registerJs(<<<JS
    $('#sendServiceBtn').click(function(e) {
        $.fancybox({
            'href': '$sendServiceUrl', 
            'type' : 'iframe',
            'height' : '300px',
            'fitToView' : false,
            'autoSize' : false
        });
    });

    $('#sendBuyerBtn').click(function(e) {
        $.fancybox({
            'href': '$sendBuyerUrl', 
            'type' : 'iframe',
            'height' : '300px',
            'fitToView' : false,
            'autoSize' : false
        });
    });
JS
    , yii\web\View::POS_READY); ?>

<?php ActiveForm::end(); ?>
