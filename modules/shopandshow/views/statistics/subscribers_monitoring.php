<?php
/* @var $this yii\web\View */

/** @var $searchModel \modules\shopandshow\models\users\UserEmailSearch */
/** @var $dataProvider \yii\data\ActiveDataProvider */

use modules\shopandshow\models\users\UserEmail;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;


$form = ActiveForm::begin([
    'action'    => Url::to(['/~sx/shopandshow/statistics/subscribers-monitoring']),
    'enableAjaxValidation' => false,
    'method'    => 'get'
]);

echo $form->field($searchModel, 'dateFrom')->widget(
    \kartik\date\DatePicker::class,
    [
        'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
        ]
    ]
);
echo $form->field($searchModel, 'dateTo')->widget(
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

echo '<hr>';

echo \skeeks\cms\modules\admin\widgets\GridView::widget([
    'dataProvider'  => $dataProvider,
    'filterModel'   => $searchModel,
    'columns'       => [
        ['class' => 'yii\grid\SerialColumn'],
        'created_at:datetime:Дата добавления',
        'value:text:Email',
        [
            'label' => 'Источник',
            'attribute' => 'source',
            'value' => function($row){
                return UserEmail::getSourceLabel($row['source']);
            },
            'filter' => array_map(
                function($source){
                    return UserEmail::getSourceLabel($source['source'])." [{$source['num']}]";
                },
                $searchModel->getNonEmptySources($dataProvider)
            ),
        ],
        [
            'label' => 'Источник детально',
            'attribute' => 'source_detail',
            'value' => function($row){
                return UserEmail::getSourceDetailLabel($row['source_detail']);
            },
            'filter' => array_map(
                function($source){
                    return UserEmail::getSourceDetailLabel($source['source_detail'])." [{$source['num']}]";
                },
                $searchModel->getNonEmptySourcesDetail($dataProvider)
            ),
        ],
        [
            'label' => 'Email валидный (сайт)',
            'attribute' => 'is_valid_site',
            'class'    => \skeeks\cms\grid\BooleanColumn::className(),
        ]
    ]
]);

ActiveForm::end();

?>