<?php
use common\models\cmsContent\ContentElementFaq;

/* @var $message string */
/* @var $this yii\web\View */
/* @var $searchModel \skeeks\cms\models\Search */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>

<? $pjax = \skeeks\cms\modules\admin\widgets\Pjax::begin(); ?>


<br><br>

<?php
/*$this->render('_search', [
    'searchModel'   => $searchModel,
    'dataProvider'  => $dataProvider
]); */
?>

<?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
    'dataProvider'      => $dataProvider,
    'filterModel'       => $searchModel,
    'pjax'              => $pjax,
    'adminController'   => null, //\Yii::$app->controller,
    'enabledCheckbox'   => false,
    'settingsData' =>
        [
            'order' => SORT_DESC,
            'orderBy' => "id",
        ],
    'rowOptions' => function (ContentElementFaq $faq) {
        // опубликованные
        if ($faq->status == ContentElementFaq::STATUS_APPROVED){
            return ['class' => 'active_faq'];
        }
        // появились новые данные с даты последнего просмотра
        elseif (\common\helpers\User::isEditor() && $faq->updated_at > $faq->editor_lastview_at) {
            return ['class' => 'new_data'];
        }
        return [];
    },
    'columns' =>
        [
            [
                'class' => \skeeks\cms\modules\admin\grid\ActionColumn::class,
                'controller' => \Yii::$app->createController('/shopandshow/questions/questions')[0],
            ],
            'id',
            [
                'attribute'     => 'created_at',
                'class'         => \skeeks\cms\grid\CreatedAtColumn::className(),
            ],
            [
                'attribute'     => 'created_by',
                'class'         => \skeeks\cms\grid\CreatedByColumn::className(),
            ],
            [
                'attribute'     => 'updated_at',
                'class'         => \skeeks\cms\grid\UpdatedAtColumn::className(),
            ],
            [
                'attribute'     => 'updated_by',
                'class'         => \skeeks\cms\grid\UpdatedByColumn::className(),
            ],
            [
                'attribute'     => 'published_at',
                'class'         => \skeeks\cms\grid\PublishedAtColumn::className(),
            ],
            'username',
            'email',
            [
                'attribute' => 'question',
                'format' => 'text',
                'filter' => false,
                'enableSorting' => false
            ],
            'like',
            [
                'attribute' => 'status',
                'format' => 'raw',
                'content' => function(ContentElementFaq $data){
                    return $data->getStatus();
                },
                'filter' => ContentElementFaq::getStatusList()
            ],
            'element_id',
            [
                'attribute' => 'element_id',
                'format' => 'raw',
                'value' => function (ContentElementFaq $faq) {
                    return \yii\bootstrap\Html::a(
                        'Открыть',
                        $faq->element ? $faq->element->getUrl() : '',
                            ['target' => '_blank', 'data-pjax' => 0]
                    );

                },
                'filter' => false,
                'enableSorting' => false
            ],
        ]
]); ?>

<? $pjax::end(); ?>

<?php
$this->registerCss('
    tr.active_faq td {
        background-color: #dff0d8 !important;
    }
    
    tr.new_data td {
        background-color: #ffd699 !important;
    }
');
?>