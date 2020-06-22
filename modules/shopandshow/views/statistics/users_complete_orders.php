<?php

use yii\bootstrap\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model \modules\shopandshow\models\statistic\UserStatistics */

?>

<div class="h3">Выкупленные и не выкупленные заказы клиентов</div>

<?php $form = ActiveForm::begin([
		'action'    => \skeeks\cms\helpers\UrlHelper::construct(['statistics/user-complete-orders/'])
        ->enableAdmin()
        ->normalizeCurrentRoute()->toString(),
    'enableAjaxValidation' => false,
    'method' => 'GET',
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
                <?= $form->field($model, 'orderSource')->radioList([
                    'all'   => 'Все',
                    \modules\shopandshow\models\shop\ShopOrder::SOURCE_SITE  => 'Сайт',
                    \modules\shopandshow\models\shop\ShopOrder::SOURCE_BITRIX  => 'Телефон',
                ]) ?>
						</div>
				</div>
		</div>

<?= Html::submitButton("Показать", [
    'class' => 'btn btn-primary',
    'name' => 'show',
    'value' => 1
]); ?>

<?= Html::a("Экспортировать", \skeeks\cms\helpers\UrlHelper::construct(['statistics/user-complete-orders/'])
    ->enableAdmin()
    ->addData([
    		Html::getInputName($model, 'dateFrom') => $model->dateFrom,
		    Html::getInputName($model, 'dateTo') => $model->dateTo,
		    Html::getInputName($model, 'orderSource') => $model->orderSource,
		    'export' => 1
    ])
    ->normalizeCurrentRoute()->toString(),
		[
    		'data-pjax'    => 0,
        'class' => 'btn btn-success'
		]);
?>

    <hr>

<?
$dataProvider = $model->getOrdersByStatusData();
$usersOrdersCompleteDataProvider = $model->getOrdersCompleteData($dataProvider);

$totalStat = $model->getOrdersCompleteTotalStat($usersOrdersCompleteDataProvider);

?>

		<div class="well" style="font-size: 14px;">
				<table class="table">
						<thead>
						<tr>
								<th></th>
								<th>Заказов всего</th>
								<th>Выкуплено</th>
								<th>% выкупа</th>
						</tr>
						</thead>
						<tbody>
								<tr>
										<th>По кол-ву</th>
										<td><?= \Yii::$app->formatter->asDecimal($totalStat['orders_num']['total']) ?></td>
										<td><?= \Yii::$app->formatter->asDecimal($totalStat['orders_num']['complete']) ?></td>
										<td><?= \Yii::$app->formatter->asPercent($totalStat['complete_percent']['by_num'] / 100, 2) ?></td>
								</tr>
								<tr>
										<th>По сумме</th>
										<td><?= \Yii::$app->formatter->asDecimal($totalStat['orders_sum']['total']) ?></td>
										<td><?= \Yii::$app->formatter->asDecimal($totalStat['orders_sum']['complete']) ?></td>
										<td><?= \Yii::$app->formatter->asPercent($totalStat['complete_percent']['by_sum'] / 100, 2) ?></td>
								</tr>
						</tbody>
				</table>
		</div>

<?

echo \skeeks\cms\modules\admin\widgets\GridView::widget([
    'dataProvider'  => $usersOrdersCompleteDataProvider,
    'columns'       => [
        ['class' => 'yii\grid\SerialColumn'],
		    'user_id:text:UserID',
		    'user_name:text:Пользователь',
        [
            'label' => 'Всего заказов (кол-во)',
            'attribute' => 'orders_total_num',
            'value' => function($row){
                return \Yii::$app->formatter->asDecimal($row['orders_total_num']);
            }
        ],
        [
            'label' => 'Всего заказов (сумма)',
            'attribute' => 'orders_total_sum',
            'value' => function($row){
                return \Yii::$app->formatter->asDecimal($row['orders_total_sum']);
            }
        ],
        [
            'label' => 'Выкуплено заказов (кол-во)',
            'attribute' => 'orders_complete_num',
            'value' => function($row){
                return \Yii::$app->formatter->asDecimal($row['orders_complete_num']);
            }
        ],
        [
            'label' => 'Выкуплено заказов (сумма)',
            'attribute' => 'orders_complete_sum',
            'value' => function($row){
                return \Yii::$app->formatter->asDecimal($row['orders_complete_sum']);
            }
        ],
		    [
		    		'label' => 'Процент выкупа',
				    'attribute' => 'orders_complete_percent_of_total',
				    'value' => function($row){
						    return \Yii::$app->formatter->asPercent($row['orders_complete_percent_of_total'] / 100, 2);
				    }
		    ],
    ]
]);

?>

<?php ActiveForm::end(); ?>