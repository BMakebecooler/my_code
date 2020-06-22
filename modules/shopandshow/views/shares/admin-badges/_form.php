<?php

use common\helpers\ArrayHelper;
use kartik\select2\Select2;
use modules\shopandshow\widgets\StorageImage;
use skeeks\cms\modules\admin\components\UrlRule;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use yii\web\JsExpression;


/** @var $this yii\web\View */
/** @var $model \modules\shopandshow\models\shares\badges\SsBadge */

if ($model->isNewRecord && empty($model->begin_datetime)) {
    $model->active = \skeeks\cms\components\Cms::BOOL_Y;
    $model->begin_datetime = strtotime('tomorrow +7 hours');
    $model->end_datetime = $model->begin_datetime + DAYS_1 - 1;
}
?>



<?php $form = ActiveForm::begin([
    'options' => ['enctype'=>'multipart/form-data']
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
<?= $form->field($model, 'name')->textInput(); ?>
<?= $form->field($model, 'description')->textInput(); ?>
<?= $form->field($model, 'code')->textInput(); ?>
<?= $form->field($model, 'url')->textInput(); ?>
<?= $form->field($model, 'image_id')->widget(StorageImage::className()); ?>
<?= $form->field($model, 'image_id_product_card')->widget(StorageImage::className()); ?>

<?= $form->fieldSetEnd(); ?>

<?php
$products = $model->getBadgeProducts()->innerJoinWith('product')->select('id, name, code, product_id')->asArray()->all();

$model->relatedProducts = ArrayHelper::map($products, 'id', 'id');

// форматируем вывод
$products = array_map(function ($item) {
    return sprintf('[%s] %s', $item['code'], $item['name']);
}, $products);

?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Товары в акции')); ?>

<?= $form->field($model, 'updateProducts', ['options' => ['tag' => false]])->hiddenInput(['value' => true])->label(false); ?>
<?= $form->field($model, 'relatedProducts')->widget(Select2::classname(), [
    'options' => ['multiple' => true],
    'initValueText' => $products,
    'showToggleAll' => false,
    'pluginOptions' => [
        'allowClear' => true,
        'minimumInputLength' => 3,
        'language' => [
            'errorLoading' => new JsExpression("function () { return 'Ничего не найдено'; }"),
        ],
        'ajax' => [
            'url' => \yii\helpers\Url::to(['shares/admin-badges/product-search', UrlRule::ADMIN_PARAM_NAME => UrlRule::ADMIN_PARAM_VALUE]),
            'dataType' => 'json',
            'delay' => 500,
            'data' => new JsExpression('function(params) { return {q:params.term}; }')
        ],
        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
        'templateResult' => new JsExpression('function(dat) { if(dat.code || dat.name) return "["+dat.code+"] "+dat.name; else return "Идет поиск..." }'),
        'templateSelection' => new JsExpression('function (dat) { return dat.code ? "["+dat.code+"] "+dat.name : dat.text; }'),
    ],
])->label(false); ?>

<? \yii\bootstrap\Alert::begin(['options' => ['class' => 'alert-warning']]); ?>
При загрузке из файла все лоты полностью перезаписываются
<? \yii\bootstrap\Alert::end(); ?>

<?= $form->field($model, 'updateFile')->fileInput(['accept' => ".csv"])->hint('* лоты в файле указываются 1 шт. на строку. Вторым столбцом можно указывать значения для текстовых плашек.') ?>

<?= $form->fieldSetEnd(); ?>


<?= \yii\helpers\Html::button('Очистить текстовые плашки', ['class' => 'btn btn-info', 'id' => 'clear-badge-text']); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>

<?

//$adminBadgesUrl = \yii\helpers\Url::to(['']);

$this->registerJs(<<<JS

$(function(){
    $('#clear-badge-text').click(function(){
        
        if( confirm('Вы уверены?') ){
            var ajax = sx.ajax.preparePostQuery('/~sx/shopandshow/shares/admin-badges/clear-badge-text/');
            
            ajax.setData({
                'badgeId': [{$model->id}]
            });

            ajax.onSuccess(function (e, data) {
                if (data.response.success){
                    $.jGrowl('Выполнено. ' + data.response.message);
                }else{
                    $.jGrowl(data.response.message);
                }
            });
            ajax.execute();
        }
    });
});

JS
);


?>

<?php ActiveForm::end(); ?>
