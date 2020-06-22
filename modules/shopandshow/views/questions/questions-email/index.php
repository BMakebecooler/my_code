<?php
use modules\shopandshow\models\questions\QuestionEmail;

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
    'adminController'   => \Yii::$app->controller,
    'enabledCheckbox'   => true,
    'settingsData' =>
        [
            'order' => SORT_DESC,
            'orderBy' => "id",
        ],
    'columns' =>
        [
            [
                'attribute' => 'group',
                'format' => 'raw',
                'content' => function(QuestionEmail $data){
                    return $data->getGroup();
                },
                'filter' => QuestionEmail::getGroupList()
            ],
            [
                'attribute' => 'type',
                'format' => 'raw',
                'content' => function(QuestionEmail $data){
                    return $data->getType();
                },
                'filter' => QuestionEmail::getTypeList()
            ],
            [
                'attribute' => 'tree_id',
                'format' => 'raw',
                'value' => function (QuestionEmail $data) {
                    $result = '';
                    if (empty($data->tree_id)) {
                        return $result;
                    }

                    $tree = \common\lists\TreeList::getTreeById($data->tree_id);
                    foreach ($tree->parents as $node) {
                        if ($node->tree_type_id == CATALOG_TREE_TYPE_ID) {
                            $result .= $node->name.' &gt; ';
                        }
                    }
                    $result .= $tree->name;
                    return $result;
                },
                'filter' => false,
                'enableSorting' => false
            ],
            'fio',
            'email'
        ]
]); ?>

<? $pjax::end(); ?>
