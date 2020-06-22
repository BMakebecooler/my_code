<?php
/* @var $message string */
/* @var $this yii\web\View */
/* @var $searchModel \skeeks\cms\models\Search */
/* @var $dataProviderAllLogs yii\data\ActiveDataProvider */
/* @var $dataProviderReport yii\data\ActiveDataProvider */
?>

<? $pjax = \skeeks\cms\modules\admin\widgets\Pjax::begin(); ?>

    <br><br>

<?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
    'dataProvider' => $dataProviderReport,
    'filterModel' => $searchModel,
    'pjax' => $pjax,
//    'adminController' => \Yii::$app->controller,
    'enabledCheckbox' => false,
    'columns' => [
//        ['class' => 'yii\grid\SerialColumn'],
        [
            'label' => "Дата - время",
            'attribute' => 'date',
            'format' => 'raw',
            'value' => function ($data) {
                return date('d.m.Y H:i', strtotime($data["date"]));
            }
        ],
        [
            'label' => "Кол-во",
            'attribute' => 'count',
            'value' => function ($data) {
                return $data["count"];
            }
        ],
    ],
]);
?>

    <br><br>

<?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
    'dataProvider' => $dataProviderAllLogs,
    'filterModel' => $searchModel,
    'pjax' => $pjax,
//    'adminController' => \Yii::$app->controller,
    'enabledCheckbox' => false,
    'columns' => [
//        ['class' => 'yii\grid\SerialColumn'],
        [
            'label' => "Дата",
            'attribute' => 'created_at',
            'format' => 'raw',
            'value' => function ($data) {
                return date('d.m.Y H:i', strtotime($data["created_at"]));
            }
        ],
        [
            'label' => "Телефон",
            'attribute' => 'phone',
            'value' => function ($data) {
                return $data["phone"];
            }
        ],
        [
            'label' => "Товары",
            'attribute' => 'products',
            'value' => function ($data) {
                return $data["products"];
            }
        ],
    ],
]);
?>

<? $pjax::end(); ?>

<?php
$this->registerCss('
    tr.active_share td {
        background-color: #dff0d8 !important;
    }
');
?>