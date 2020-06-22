<?php

/* @var $this yii\web\View */
use yii\helpers\Html;

/* @var $searchModel \skeeks\cms\models\Search */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model \modules\shopandshow\models\shop\stock\SegmentFile */

?>
<? $pjax = \yii\widgets\Pjax::begin(); ?>


<?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'autoColumns' => false,
    'pjax' => $pjax,
    'adminController' => $controller,
    'columns' =>
        [
            'id',
            [
                'label' => 'Кто загрузил',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->createdBy->name;
                },
            ],
            'name',
            [
                'label' => 'Файл',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->file_id ? Html::a($data->file->original_name, $data->file->src, ['data-pjax' => '0']) : '';
                },
            ],
            [
                'label' => 'Дата начала',
                'format' => 'raw',
                'value' => function ($data) {
                    return date('d-m-Y H:i:s', $data->begin_datetime);
                },
            ],
            [
                'label' => 'Дата окончания',
                'format' => 'raw',
                'value' => function ($data) {
                    return date('d-m-Y H:i:s', $data->end_datetime);
                },
            ]
        ]
]); ?>

<? \yii\widgets\Pjax::end(); ?>
