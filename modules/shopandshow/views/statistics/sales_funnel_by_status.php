<?php

use miloschuman\highcharts\Highcharts;

use yii\bootstrap\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model \modules\shopandshow\models\statistic\SalesFunnelByStatus */

$this->registerCss(<<<CSS
.sales-funnel-top-info {

}
.sales-funnel-top-info tr td:first-child {
  border-right: 1px solid black;
  text-align: right;
}
.sales-funnel-top-info tr td {
	padding: 1px 3px;
}
CSS
);

?>

<div class="h3">Воронка продаж по статусам заказов</div>

<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => false,
    'method' => 'POST',
]); ?>

<?= $form->field($model, 'dateFrom')->widget(
    \kartik\date\DatePicker::class,
    [
        'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
        ]
    ]
); ?>

<?= $form->field($model, 'dateTo')->widget(
    \kartik\date\DatePicker::class,
    [
        'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
        ]
    ]
); ?>

<div class="container-fluid">
		<div class="row">
				<div class="col-md-4">
						<?= $form->field($model, 'sourceDetail')->radioList([
								'all'   => 'Все',
								'site'  => 'Настольная',
								'mobile'  => 'Мобильная',
						]) ?>
				</div>
				<div class="col-md-4">
            <?= $form->field($model, 'userType')->radioList([
                'all'   => 'Все',
                'registered'  => 'Зарегистрированный',
                'fast_order'  => 'Быстрый заказ',
            ]) ?>
				</div>
				<div class="col-md-4">
            <?= $form->field($model, 'orderSource')->radioList([
                'all'   => 'Все',
                'site'  => 'Корзина',
                'site_phone'  => 'Телефон',
            ]) ?>
				</div>
		</div>
</div>

<?= Html::submitButton("Показать", [
    'class' => 'btn btn-primary',
    'name' => 'show',
    'value' => 1
]); ?>

    <hr>

		<div class="well" style="font-size: 14px;">
				<div>Сверху на графике - Кол-во заказов | Процент от первого статуса (абсолютные значения)</div>
				<div>Снизу на графике - Процент от предыдущего статуса (относительные значения)</div>
		</div>

<?

$byDateDataProvider = $model->getByStatusDataProvider();

$hcSeriesWithDrilldownData = $model->getSeriesForHighcharts($byDateDataProvider->allModels);
$highchartsConfig = $model->getHighchartsConfig($hcSeriesWithDrilldownData);

?>

<?php echo Highcharts::widget($highchartsConfig); ?>


<?php ActiveForm::end(); ?>