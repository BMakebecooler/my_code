<?php

use miloschuman\highcharts\Highcharts;
use modules\shopandshow\models\sms\Sms;
use modules\shopandshow\models\statistic\PassTime;

use yii\bootstrap\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model PassTime */

?>

		<div class="h3">Время от запроса пароля до успешной авторизации</div>

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

<?= Html::submitButton("Показать", [
    'class' => 'btn btn-primary',
    'name' => 'show',
    'value' => 1
]); ?>

		<hr>

<?
//* Авторизация *//

$getPassRequestsNum = (int)Sms::find()
		->where(['type' => \common\components\sms\Sms::SMS_TYPE_GET_PASSWORD])
		->andWhere(['>=', 'created_at', $model->dateFrom . ' 00:00:00'])
		->andWhere(['<=', 'created_at', $model->dateTo . ' 23:59:59'])
		->count();

//Полный набор "сырых" данных
$passTimeData = $model->getDataBySeconds();
$hcSeriesWithDrilldownData = $model->getSeriesWithDrilldownForHighcharts($passTimeData);
$highchartsConfig = $model->getHighchartsConfig($hcSeriesWithDrilldownData['series'], $hcSeriesWithDrilldownData['drilldownSeries']);

$passTimeItemsNum = \common\helpers\ArrayHelper::arraySumColumn($passTimeData, 'quantity');
$passTimeSeconds = \common\helpers\ArrayHelper::getColumn($passTimeData, 'seconds');

$passTimeMin = $passTimeSeconds ? min($passTimeSeconds) : 0;
$passTimeMax = $passTimeSeconds ? max($passTimeSeconds) : 0;

//* /Авторизация *//

//* Ввод первого символа пароля при авторизации после запроса пароля *//

//Полный набор "сырых" данных
$passTimeDataFirstSymbol = $model->getDataBySecondsFirstSymbol();

$hcSeriesWithDrilldownDataFirstSymbol = $model->getSeriesWithDrilldownForHighcharts($passTimeDataFirstSymbol);
$highchartsConfigFirstSymbol = $model->getHighchartsConfig(
		$hcSeriesWithDrilldownDataFirstSymbol['series'],
		$hcSeriesWithDrilldownDataFirstSymbol['drilldownSeries'],
		[
				'options'   => [
						'title' => ['text' => 'Ввод первого символа пароля при его запросе через смс']
				]
		]
);

$passTimeFirstSymbolItemsNum = \common\helpers\ArrayHelper::arraySumColumn($passTimeDataFirstSymbol, 'quantity');
$passTimeFirstSymbolSeconds = \common\helpers\ArrayHelper::getColumn($passTimeDataFirstSymbol, 'seconds');

$passTimeFirstSymbolMin = $passTimeFirstSymbolSeconds ? min($passTimeFirstSymbolSeconds) : 0;
$passTimeFirstSymbolMax = $passTimeFirstSymbolSeconds ? max($passTimeFirstSymbolSeconds) : 0;

//* /Ввод первого символа пароля при авторизации после запроса пароля *//

?>

<div class="well" style="font-size: 15px;">
		<table class="table" style="width: auto;">
				<tr>
						<th>Всего запросов пароля</th>
						<td><?= $getPassRequestsNum; ?></td>
				</tr>
				<tr>
						<th>Всего авторизаций</th>
						<td><?= $passTimeItemsNum; ?></td>
				</tr>
				<tr>
						<th>Минимальное время авторизации</th>
						<td><?= $passTimeMin; ?> сек.</td>
				</tr>
				<tr>
						<th>Максимальное время авторизации</th>
						<td><?= $passTimeMax; ?> сек.</td>
				</tr>
				<tr>
						<th>Минимальное время до ввода первого символа</th>
						<td><?= $passTimeFirstSymbolMin; ?> сек.</td>
				</tr>
				<tr>
						<th>Максимальное время до ввода первого символа</th>
						<td><?= $passTimeFirstSymbolMax; ?> сек.</td>
				</tr>
		</table>
</div>

<?php echo Highcharts::widget(array_merge($highchartsConfig, ['scripts' => ['modules/drilldown']])); ?>
<hr>
<?php echo Highcharts::widget(array_merge($highchartsConfigFirstSymbol, ['scripts' => ['modules/drilldown']])); ?>

<?php ActiveForm::end(); ?>