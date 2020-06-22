<?php

use modules\shopandshow\models\shares\badges\SsBadge;

/* @var $message string */
/* @var $this yii\web\View */
/* @var $searchModel \skeeks\cms\models\Search */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>

<? $pjax = \skeeks\cms\modules\admin\widgets\Pjax::begin(); ?>

<?php
$dataProvider->setSort([
    'attributes' => [
        'begin_datetime' => [
            'asc' => ['begin_datetime' => SORT_ASC, 'id' => SORT_ASC],
            'desc' => ['begin_datetime' => SORT_DESC, 'id' => SORT_DESC],
            'default' => SORT_DESC
        ]
    ],
    'defaultOrder' => [
        'begin_datetime' => SORT_DESC
    ]
]);

?>

    <br><br>

<?php echo $this->render('_search', [
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider
]); ?>

<?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'pjax' => $pjax,
    'adminController' => null, //\Yii::$app->controller,
    'enabledCheckbox' => false,
    'settingsData' =>
        [
            'order' => SORT_DESC,
            'orderBy' => "begin_datetime"
        ],
    'rowOptions' => function (SsBadge $ssBadge) {
        if ($ssBadge->begin_datetime < time() && time() < $ssBadge->end_datetime) {
            return ['class' => 'active_share'];
        }
    },
    'columns' =>
        [
            [
                'class' => \skeeks\cms\modules\admin\grid\ActionColumn::className(),
                'controller' => \Yii::$app->createController('/shopandshow/shares/admin-badges')[0],
            ],
            'id',
            [
                'attribute' => 'image_id',
                'class' => \modules\shopandshow\grid\ImageUpdColumn::className(),
                'label'  => 'Изображение в каталоге',
            ],
            [
                'attribute' => 'image_id_product_card',
                'class' => \modules\shopandshow\grid\ImageUpdColumn::className(),
		            'label'  => 'Изображение в карточке',
		            'relationName'  => 'imageProductCard'
            ],
            [
                'attribute' => 'begin_datetime',
                'class' => \skeeks\cms\grid\DateTimeColumnData::className(),
            ],
            [
                'attribute' => 'end_datetime',
                'class' => \skeeks\cms\grid\DateTimeColumnData::className(),
            ],
            'name',
            'code',
            [
                'attribute' => 'active',
                'class' => \skeeks\cms\grid\BooleanColumn::className(),
            ],
            [
                'attribute' => 'url',
                'format' => 'raw',
                'value' => function (SsBadge $ssBadge) {
                    if ($url = $ssBadge->url) {
                        return \yii\bootstrap\Html::a(
                            'Перейти', $url,
                            ['target' => '_blank', 'data-pjax' => 0]
                        );
                    }

                    return '';

                }
            ],
            [
                'format' => 'raw',
                'label' => 'Лотов в акции',
                'value' => function (SsBadge $badge) {
                    return $badge->productsCount;
                }
            ]
        ]
]); ?>

<? $pjax::end(); ?>

<?php
$this->registerCss('
    tr.active_share td {
        background-color: #dff0d8 !important;
    }
');
?>