<?php

use common\helpers\ArrayHelper;
use kartik\select2\Select2;
use modules\shopandshow\models\shares\SsShareSchedule;
use modules\shopandshow\models\shares\SsShareType;
use modules\shopandshow\widgets\StorageImage;
use skeeks\cms\modules\admin\components\UrlRule;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use skeeks\cms\widgets\formInputs\comboText\ComboTextInputWidget;
use yii\web\JsExpression;


/** @var $this yii\web\View */
/** @var $model \modules\shopandshow\models\shares\SsShare */

if ($model->isNewRecord && empty($model->begin_datetime)) {
  $model->active = \skeeks\cms\components\Cms::BOOL_Y;
  $model->begin_datetime = strtotime('tomorrow +7 hours');
  $model->end_datetime = $model->begin_datetime + DAYS_1 - 1;
}
?>



<?php $form = ActiveForm::begin([
  'options' => ['enctype' => 'multipart/form-data']
]); ?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Main')); ?>

<?= $form->fieldCheckboxBoolean($model, 'active'); ?>
<?= $form->field($model, 'begin_datetime')->widget(
  \kartik\datecontrol\DateControl::class,
  [
    'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
    'displayFormat' => 'php:Y-m-d H:i:s',
  ]
); ?>
<?= $form->field($model, 'end_datetime')->widget(
  \kartik\datecontrol\DateControl::class,
  [
    'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
    'displayFormat' => 'php:Y-m-d H:i:s',
  ]
); ?>
<?= $form->fieldSelect(
  $model,
  'banner_type',
  ArrayHelper::map(SsShareType::find()->all(), 'code', 'description')
); ?>
<?= $form->fieldSelect(
  $model,
  'share_schedule_id',
  ArrayHelper::merge(
    [-1 => ' -- Отвязать от сетки -- '],
    ArrayHelper::map(
      SsShareSchedule::getAvailSchedulesForBanner($model),
      'id',
      'displayName'
    )
  )
)->hint('Оставьте пустым, чтобы подходящий блок выбрался автоматически');
?>
<?= $form->field($model, "schedule_tree_id")->dropDownList(
  \common\helpers\ArrayHelper::merge(
    [0 => ' --- Нет ---'],
    \modules\shopandshow\models\mediaplan\AirBlock::getAvailSections()
  )
); ?>
<?= $form->field($model, 'name')->textInput(); ?>
<?= $form->field($model, 'description')->textInput(); ?>
<?= $form->field($model, 'code')->textInput(); ?>
<?= $form->field($model, 'url')->textInput(); ?>
<?= $form->field($model, 'bitrix_product_id')->textInput(); ?>
<?= $form->field($model, 'image_product_id')->textInput(); ?>
<?= $form->field($model, 'is_hidden_catalog')->checkbox(); ?>
<?= $form->field($model, 'image_id')->widget(StorageImage::className()); ?>

<?= $form->fieldSetEnd(); ?>


<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Товары в акции')); ?>

<?php
$products = $model->getShareProducts()->innerJoinWith('product')->select('id, name, code, product_id')->asArray()->all();
?>
<a href="#product-list" data-toggle="collapse" class="btn btn-primary">Лотов загружено: <?= sizeof($products); ?></a>
<div class="collapse alert-info" id="product-list">
  <? foreach ($products as $product): ?>
    <?= sprintf('[%s] %s', $product['code'], $product['name']); ?><br>
  <? endforeach; ?>
</div>

<br><br>
<? \yii\bootstrap\Alert::begin(['options' => ['class' => 'alert-warning']]); ?>
При загрузке из файла все лоты полностью перезаписываются
<? \yii\bootstrap\Alert::end(); ?>

<?= $form->field($model, 'updateProducts', ['options' => ['tag' => false]])->hiddenInput(['value' => true])->label(false); ?>
<?= $form->field($model, 'updateFile')->fileInput(['accept' => ".csv"])->hint('* лоты в файле указываются в формате 000-000-000 (1 шт. на строку)') ?>

<?= $form->fieldSetEnd(); ?>



<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Промо баннер')); ?>

<?= $form->field($model, 'cce_image_id')->widget(StorageImage::className()); ?>
<?= $form->field($model, 'cce_description')->widget(
  ComboTextInputWidget::className(), ['defaultEditor' => ComboTextInputWidget::CONTROLL_EDITOR]
); ?>

<?= $form->fieldSetEnd(); ?>




<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
