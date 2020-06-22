<?php
/* @var $this yii\web\View */
/* @var $model modules\shopandshow\models\Shares\SharesStat */

/* @var $dataProvider yii\data\ArrayDataProvider */

use yii\bootstrap\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

$dataProvider = $model->getBannersByDateAndTypeData();
$dataProviderByDateAndType = $model->getBannersByDateAndTypeGridData($dataProvider);

if (!empty($noGridView) && $noGridView === true) {

    //Блок для сборки тела письма

} else {

    $form = ActiveForm::begin([
        'enableAjaxValidation' => false,
    ]);

    echo $form->field($model, 'dateFrom')->widget(
        \kartik\date\DatePicker::class,
        [
            'pluginOptions' => [
                'format' => 'yyyy-mm-dd',
            ]
        ]
    );
    echo $form->field($model, 'dateTo')->widget(
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

    $metriks = [
        'ctr'           => 'CTR',
        'views'         => 'Просмотры',
        'count_click'   => 'Клики',
        'price'         => 'Продажи'
    ];

    foreach ($metriks as $metrik => $metrikName) {

        $columns = [
            [
                'class' => \yii\grid\SerialColumn::class,
                'headerOptions'    => ['style' => 'width: 40px;'],
            ],
            [
                'label' => 'Тип баннера/блока',
                'attribute' => 'block_type',
            ]

        ];

        //* Динамические колонки *//

        $dateTo = DateTime::createFromFormat('Y-m-d H:i:s', $model->dateTo . ' 23:59:59');
        $date = (new DateTime())->setTimestamp($model->timeFrom);
        while ($date <= $dateTo){
            $dateStr = $date->format("Y-m-d");
            $columns[] = [
                'label' => $date->format("Y-m-d"),
                'format'    => 'raw',
                'value' => function($row) use ($dateStr, $metrik){
                    return !empty($row['date_'.$dateStr]) ? $row['date_'.$dateStr][$metrik] : '&mdash;';
                }
            ];

            $date->add(new DateInterval('P1D'));
        }

        //* /Динамические колонки *//

        if ($metrik != 'ctr'){
            $columns[] = [
                'label' => 'ВСЕГО',
                'format'    => 'raw',
                'contentOptions'    => ['style' => 'font-weight: 700;'],
                'value' => function($row) use ($metrik){
                    return $row['total'][$metrik] ?: '&mdash;';
                }
            ];
        }

        $columns[] = [
            'label' => 'Среднее за период',
            'contentOptions'    => ['style' => 'font-weight: 700;'],
            'format'    => 'raw',
            'value' => function($row) use ($metrik){
                if ($metrik == 'ctr'){
                    return $row['avg'][$metrik];
                }else{
                    return $row['avg'][$metrik] ?: '&mdash;';
                }
            }
        ];

        echo "<div class='h3'>{$metrikName}</div>" .
            \skeeks\cms\modules\admin\widgets\GridView::widget([
            'dataProvider' => $dataProviderByDateAndType,
            'layout'    => '{items}',
            'tableOptions'  => [
                'style' => 'table-layout: fixed;',
                'class' => 'table table-striped table-bordered sx-table'
            ],
            'columns' => $columns
        ]);

        echo '<hr>';
    }

    ActiveForm::end();
}

?>