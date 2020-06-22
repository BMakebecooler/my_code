<?php

use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use skeeks\cms\modules\admin\components\UrlRule;
use kartik\select2\Select2;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model modules\shopandshow\models\shop\shopdiscount\ConfigurationValue */
/* @var $form ActiveForm */
/* @var $entity modules\shopandshow\models\shop\shopdiscount\Entity */

?>

<div id="entity-id<?=$entity->id?>" class="entity-param" style="display: none;">
    <?= $form->field($model, 'value')->widget(Select2::classname(), [
        'options' => ['multiple' => true],
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
            'templateSelection' => new JsExpression('function (dat) { return dat.code || dat.name; }'),
        ],
    ]); ?>
</div>
