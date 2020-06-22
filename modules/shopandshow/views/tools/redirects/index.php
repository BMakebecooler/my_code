<?php

use yii\grid\GridView;

?>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
//    'filterModel' => $searchModel,
    'tableOptions' => [
        'class' => 'table table-bordered'
    ],
    'rowOptions' => function ($model, $key, $index, $grid) {
        //$class = \common\helpers\Dates::between(date('U'), $model->date_from, $model->date_to) && $model->active ? 'bg-success' : '';
        $class = '';
        if (!$class) {
            $class = $index % 2 ? 'odd' : 'even';
        }
        return [
            'key' => $key,
            'index' => $index,
            'class' => $class
        ];
    },
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        'id',
        'from',
        'to',
        ['class' => 'yii\grid\ActionColumn'],
    ],
]); ?>