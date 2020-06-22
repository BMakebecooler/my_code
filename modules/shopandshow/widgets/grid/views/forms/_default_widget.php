<?php

use kartik\select2\Select2;
use yii\web\JsExpression;
use skeeks\cms\modules\admin\components\UrlRule;

/* @var $this yii\web\View */
/* @var $model \modules\shopandshow\widgets\grid\DefaultWidget */



?>
<?= $form->fieldSet(\Yii::t('app', 'Параметры')); ?>
<?= $form->field($model, 'header')->textInput(); ?>
<?= $form->field($model, 'subHeader')->textInput(); ?>

<?= $form->field($model, 'image_id')->widget(\modules\shopandshow\widgets\StorageImage::className()); ?>

<?= $form->field($model, 'imageUrl')->textInput(); ?>
<?= $form->field($model, 'imageTitle')->textInput(); ?>

<?= $form->field($model, 'button')->checkbox(); ?>
<?= $form->field($model, 'buttonUrl')->textInput(); ?>
<?= $form->field($model, 'buttonTitle')->textInput(); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Лоты')); ?>
<?php

// предзагрузка названий элементов для отображения в виджете
$products = [];
if ($model->products) {
    $products = \common\models\cmsContent\CmsContentElement::find()
        ->select('id, code, name')
        ->andWhere(['id' => $model->products])
        ->orderBy(new \yii\db\Expression('FIELD(id, '.join(',', $model->products).')'))
        ->asArray()->all();

    // форматируем вывод
    $products = array_map(function ($item) {
        return sprintf('[%s] %s', $item['code'], $item['name']);
    }, $products);
}

?>
<?= $form->field($model, 'products')->widget(Select2::classname(), [
    'options' => ['multiple' => true],
    'initValueText' => $products,
    'pluginOptions' => [
        'allowClear' => true,
        'minimumInputLength' => 3,
        'language' => [
            'errorLoading' => new JsExpression("function () { return 'Ничего не найдено'; }"),
        ],
        'ajax' => [
            'url' => \yii\helpers\Url::to(['/shopandshow/mail/admin-schedule/product-search', UrlRule::ADMIN_PARAM_NAME => UrlRule::ADMIN_PARAM_VALUE]),
            'dataType' => 'json',
            'delay' => 500,
            'data' => new JsExpression('function(params) { return {q:params.term}; }')
        ],
        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
        'templateResult' => new JsExpression('function(dat) { if(dat.code || dat.name) return "["+dat.code+"] "+dat.name; else return "Идет поиск..." }'),
        'templateSelection' => new JsExpression('function (dat) { return dat.code ? dat.code || dat.name : dat.text; }'),
    ],
]); ?>
<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Служебные')); ?>
<?= $form->field($model, 'viewFile')->textInput(); ?>
<?= $form->fieldSetEnd(); ?>


