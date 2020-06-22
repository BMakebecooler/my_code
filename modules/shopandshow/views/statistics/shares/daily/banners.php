<?php
/* @var $this yii\web\View */
/* @var $model modules\shopandshow\models\Shares\SharesStat */
/* @var $form ActiveForm */
/* @var $dataProvider yii\data\ArrayDataProvider */

use modules\shopandshow\models\statistic\Statistics;
use yii\widgets\ActiveForm;

$dataProvider = $model->getDataProvider();

//var_dump($dataProvider);
//die();

$dataProvider->setSort([
    'attributes' => [
        'id' => [
            'asc' => ['id' => SORT_ASC],
            'desc' => ['id' => SORT_DESC],
            'default' => SORT_DESC
        ],
        'grid_banner_num' => [
            'asc' => ['grid_banner_num' => SORT_ASC],
            'desc' => ['grid_banner_num' => SORT_DESC],
            'default' => SORT_ASC
        ],
        'grid_block_num' => [
            'asc' => ['grid_block_num' => SORT_ASC],
            'desc' => ['grid_block_num' => SORT_DESC],
            'default' => SORT_ASC
        ],
        'name' => [
            'asc' => ['name' => SORT_ASC],
            'desc' => ['name' => SORT_DESC],
            'default' => SORT_ASC
        ],
        'views' => [
            'asc' => ['views' => SORT_ASC],
            'desc' => ['views' => SORT_DESC],
            'default' => SORT_DESC
        ],
        'count_click' => [
            'asc' => ['count_click' => SORT_ASC],
            'desc' => ['count_click' => SORT_DESC],
            'default' => SORT_DESC
        ],
        'count_click_email' => [
            'asc' => ['count_click_email' => SORT_ASC],
            'desc' => ['count_click_email' => SORT_DESC],
            'default' => SORT_DESC
        ],
        'price' => [
            'asc' => ['price' => SORT_ASC],
            'desc' => ['price' => SORT_DESC],
            'default' => SORT_DESC
        ],
    ],
    'defaultOrder' => [
        'grid_banner_num' => SORT_ASC
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
            'label' => "Изображение",
            'format'    => 'raw',
            'value' => function ($row) {
                $image = '';
                if ($row['image_id']){
                    $image = yii\helpers\Html::img($row['image_src'], ['style' => 'max-width: 100%; max-height: 100px;']);
                }
                return $image;
            },
            'contentOptions' => function ($model, $key, $index, $column) {
                return ['class' => 'text-center'];
            },
        ],
        [
            'label' => "ID",
            'attribute' => 'id',
        ],
        [
            'label' => "№ в сетке",
            'attribute' => 'grid_banner_num',
        ],
        [
            'label' => "№ блока в сетке",
            'attribute' => 'grid_block_num',
        ],
        [
            'label' => "Тип блока",
            'value' => function ($row) {
                preg_match("/^BANNER_(\d+)_*(\d*)$/", $row['banner_type'], $matches);
                return !empty($matches[1]) ? $matches[1] : $row['banner_type'];
            },
        ],
        [
            'label' => "№ баннера в блоке",
            'value' => function ($row) {
                preg_match("/^\D+(\d+)_*(\d*)$/", $row['banner_type'], $matches);
                return !empty($matches[2]) ? $matches[2] : 1;
            },
        ],
        [
            'label' => "Название",
            'attribute' => 'name',
        ],
        [
            'label' => "Показов",
            'attribute' => 'views',
            'footer' => array_sum(array_column($dataProvider->getModels(), 'views'))
        ],
        [
            'label' => "Кликов",
            'attribute' => 'count_click',
            'footer' => array_sum(array_column($dataProvider->getModels(), 'count_click'))
        ],
        [
            'label' => "Кликов (рассылка)",
            'attribute' => 'count_click_email',
            'footer' => array_sum(array_column($dataProvider->getModels(), 'count_click_email'))
        ],
        [
            'label' => "CTR",
            'value' => function ($row) {
                return \Yii::$app->formatter->asPercent(($row['count_click'] && $row['views']) ? round($row['count_click']/$row['views'], 2) : 0);
            },
        ],
        [
            'label' => "Продажи",
            'attribute' => 'price',
            'value' => function ($row) {
                return \Yii::$app->formatter->asDecimal(round($row['price']));
            },
            'footer' => \Yii::$app->formatter->asDecimal(array_sum(array_column($dataProvider->getModels(), 'price')))
        ],
    ]
]);

?>