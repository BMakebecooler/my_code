<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use modules\shopandshow\models\shop\ShopDiscount;
use skeeks\cms\modules\admin\components\UrlRule;
use kartik\select2\Select2;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model modules\shopandshow\models\shop\ShopDiscount */
/* @var $form ActiveForm */
?>

<?= $form->field($model, 'name')->textInput(); ?>
<?= $form->field($model, 'code')->textInput(); ?>

<?= $form->fieldSelect($model, 'site_id', \yii\helpers\ArrayHelper::map(
    \skeeks\cms\models\CmsSite::find()->all(), 'id', 'name'
)); ?>

<div class="discount-type">
    <?= $form->fieldSelect($model, 'value_type', $model::getValueTypes()); ?>

    <div class="discount-param" id="param-main" style="display:none">
        <?= $form->field($model, 'value')->textInput(); ?>
    </div>
    <div class="discount-param" id="param-ladder" style="display:none">
        <?= $this->render('_form_ladders', [
            'model' => $model,
            'form'  => $form
        ]); ?>
    </div>
    <div class="discount-param" id="param-gift" style="display:none">
        <?= $form->field($model, 'gift')->widget(Select2::classname(), [
            'initValueText' => $model->giftTextValue,
            'pluginOptions' => [
                'allowClear' => true,
                'minimumInputLength' => 3,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Ничего не найдено'; }"),
                ],
                'ajax' => [
                    'url' => \yii\helpers\Url::to(['shop/shopdiscount/entity-search/search', UrlRule::ADMIN_PARAM_NAME => UrlRule::ADMIN_PARAM_VALUE]),
                    'dataType' => 'json',
                    'delay' => 500,
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(dat) { if(dat.code || dat.name) return "["+dat.code+"] "+dat.name; else return "Идет поиск..." }'),
                'templateSelection' => new JsExpression('function (dat) { if(dat.code || dat.name) return "["+dat.code+"] "+dat.name; else return dat.text; }'),
            ],
        ]); ?>
    </div>
    <?= $form->field($model, 'max_discount')->textInput(); ?>
    <?= $form->fieldSelect($model, 'currency_code', \yii\helpers\ArrayHelper::map(
        \skeeks\modules\cms\money\models\Currency::find()->active()->all(), 'code', 'code'
    )); ?>
</div>

<?= $form->fieldCheckboxBoolean($model, 'active'); ?>
<?= $form->field($model, 'active_from')->widget(
    \kartik\datecontrol\DateControl::class,
    [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        'displayFormat' => 'php:Y-m-d H:i',
    ]
); ?>

<?= $form->field($model, 'active_to')->widget(
    \kartik\datecontrol\DateControl::class,
    [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        'displayFormat' => 'php:Y-m-d H:i',
    ]
); ?>

<?= $form->fieldInputInt($model, 'priority'); ?>
<?= $form->fieldCheckboxBoolean($model, 'last_discount'); ?>
<?= $form->field($model, 'notes')->textarea(['rows' => 3]); ?>

<?php
$this->registerJs('
    $(document).ready(function () {
        var discountTypeMap = {
            '.ShopDiscount::VALUE_TYPE_GIFT.': "gift",
            '.ShopDiscount::VALUE_TYPE_LADDER.': "ladder",
        };
        var entities = $(".discount-type select[name*=\'value_type\']");
        var params = $(".discount-type div.discount-param");

        $(entities).change(function () {
            var value_type = $(this).find(":selected").val();
            var id = discountTypeMap[value_type] || "main";
            var selected_div = $("#param-"+id).toArray()[0];
            
            $(params).toArray().forEach(function (div) {
                if(div == selected_div){
                    $(div).css(\'display\',\'block\');
                } else {
                    $(div).css(\'display\',\'none\');
                }
            });
        });
        
        entities.trigger(\'change\');
    })');
?>