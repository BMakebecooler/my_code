<?php
/* @var $this yii\web\View */
/* @var $model modules\shopandshow\models\Shares\SharesStat */

/* @var $dataProvider yii\data\ArrayDataProvider */

use yii\bootstrap\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

$form = ActiveForm::begin([
    'enableAjaxValidation' => false,
]);

echo $form->field($model, 'date')->widget(
    \kartik\date\DatePicker::class,
    [
        'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
        ]
    ]
);

echo Html::submitButton("Показать", [
    'class' => 'btn btn-primary',
]);

$dataProvider = $model->getDataProvider();
$shares = $dataProvider->getModels();
$blockClicks = \common\helpers\ArrayHelper::arraySumColumn($shares, 'block_clicks');

?>

<hr>


<div class="well" style="font-size: 16px;">
    <div>Суммарное кол-во кликов по всем баннерам: <strong><?= \Yii::$app->formatter->asDecimal($blockClicks); ?></strong></div>
</div>


<?php

$dataProvider->setSort([
    'attributes' => [
        'block_row_num' => [
            'asc' => ['block_row_num' => SORT_ASC],
            'desc' => ['block_row_num' => SORT_DESC],
            'default' => SORT_ASC
        ],
    ],
    'defaultOrder' => [
        'block_row_num' => SORT_ASC
    ]
]);

echo \skeeks\cms\modules\admin\widgets\GridView::widget([
    'dataProvider' => $dataProvider,
    'showFooter' => true,
    'footerRowOptions' => ['style' => 'font-weight:bold;', 'class' => 'bg-info'],
    'columns' => [
        /*
        [
            'class' => \yii\grid\SerialColumn::class,
        ],
        */
        [
            'label' => "№ Ряда",
            'attribute' => 'block_row_num',
        ],
        [
            'label' => "Тип блока",
            'attribute' => 'block_type',
            'format'    => 'raw',
            'value' => function($row){
                $bannerTypeLabel = \modules\shopandshow\models\shares\SsShare::getBannerTypeLabel($row['block_type']);
                return "<strong>{$row['block_type']}</strong>" . ($bannerTypeLabel ? "<div>{$bannerTypeLabel}</div>" : '');
            }
        ],
        [
            'label' => "Баннер 1",
            'attribute' => 'banner_1',
            'format' => 'raw',
            'contentOptions' => function ($model, $key, $index, $column) {
                return ['class' => 'text-center'];
            },
        ],
        [
            'label' => "Баннер 2",
            'attribute' => 'banner_2',
            'format' => 'raw',
            'contentOptions' => function ($model, $key, $index, $column) {
                return ['class' => 'text-center'];
            },
        ],
        [
            'label' => "Баннер 3",
            'attribute' => 'banner_3',
            'format' => 'raw',
            'contentOptions' => function ($model, $key, $index, $column) {
                return ['class' => 'text-center'];
            },
        ],
        [
            'label' => "Баннер 4",
            'attribute' => 'banner_4',
            'format' => 'raw',
            'contentOptions' => function ($model, $key, $index, $column) {
                return ['class' => 'text-center'];
            },
        ],
        [
            'label' => "Баннер 5",
            'attribute' => 'banner_5',
            'format' => 'raw',
            'contentOptions' => function ($model, $key, $index, $column) {
                return ['class' => 'text-center'];
            },
        ],
    ]
]);

ActiveForm::end();

?>