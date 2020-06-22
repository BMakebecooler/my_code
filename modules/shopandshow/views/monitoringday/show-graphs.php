<?php

use miloschuman\highcharts\Highcharts;
use modules\shopandshow\models\monitoringday\Plan;
use modules\shopandshow\models\shop\ShopOrder;
use yii\bootstrap\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model Plan */


?>

<div class="h3">Основные графики</div>
<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => false,
    'method' => 'GET',
    'action' => '/'.\Yii::$app->request->pathInfo
]); ?>
<input type="hidden" name="scroll-to-onair-graph" id="scroll-to-onair-graph" value="0">

<?= $form->field($model, 'date')->widget(
    \kartik\date\DatePicker::class,
    [
        'pluginOptions' => [
          'format' => 'yyyy-mm-dd',
      ]
    ]
); ?>

<?= $form->field($model, 'autoUpdate'); ?>

<?= Html::submitButton("Показать", [
    'class' => 'btn btn-primary',
    'name' => 'show',
    'value' => 1
]); ?>

<hr>

<?php
$zoneOffset = date('Z');
// Смещение часового пояса в минутах от GMT
$this->registerJs(<<<JS
Highcharts.setOptions({
    global: {
        timezoneOffset: -1*{$zoneOffset}/60
    }
});
JS
);

if ($model->autoUpdate > 0) {
    $delay = $model->autoUpdate*1000;
    $this->registerJs(<<<JS
    $(function() {
        _.delay(function() {
            window.location.reload(true);
    
        }, {$delay});
    });
JS
);
}

?>

<?= Highcharts::widget($model->getHighchartsData($model->getDataProvider())); ?>

<?= Highcharts::widget($model->getHighchartsDataForSource($model->getDataProviderBySource(ShopOrder::SOURCE_SITE), 'Продажи с сайта')); ?>

<?= Highcharts::widget($model->getHighchartsDataForSource($model->getDataProviderBySource(ShopOrder::SOURCE_KFSS), 'Продажи с телефона')); ?>

<?= Highcharts::widget($model->getHighchartsDataForTrafic()); ?>



<?php
$categories = \common\helpers\ArrayHelper::map($model->getCategories(), 'id', 'name');
$categories = \common\helpers\ArrayHelper::merge(['Все'], $categories);
?>
<div id="onair-graph">
    <label>
        Категория
        <?= Html::dropDownList(
            'tree_id',
            \Yii::$app->request->get('tree_id'),
            $categories,
            [
                'class' => 'form-control',
                'id' => 'tree_category',
            ]
        );?>
    </label>
    <?= $form->field($model, 'showCts', ['options' => ['tag' => false]])->checkbox(['id' => 'show_cts', 'value' => 1]); ?>
</div>
<?= Highcharts::widget($model->getHighchartsDataOnAir(\Yii::$app->request->get('tree_id'))); ?>



<? $isScrollToOnAirGraph = (int)\Yii::$app->request->get('scroll-to-onair-graph'); ?>

<? $this->registerJs(<<<JS
    (function() {
        $('#tree_category, #show_cts').on('change', function() {
            $('#scroll-to-onair-graph').val(1);
            $(this).closest("form").submit();
        });
        
        if ({$isScrollToOnAirGraph}) {
            $('#onair-graph')[0].scrollIntoView();
        }
    })();
JS
);
?>

<?php ActiveForm::end(); ?>



