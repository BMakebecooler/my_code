<?php
use modules\shopandshow\models\shares\SsShareType;

/* @var $this yii\web\View */
/* @var $searchModel \skeeks\cms\models\Search */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>

<? $pjax = \skeeks\cms\modules\admin\widgets\Pjax::begin(); ?>

<?php echo $this->render('_search', [
    'searchModel'   => $searchModel,
    'dataProvider'  => $dataProvider
]); ?>

<?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
    'dataProvider'      => $dataProvider,
    'filterModel'       => $searchModel,
    'pjax'              => $pjax,
    'adminController'   => \Yii::$app->controller,
    'enabledCheckbox'   => false,
    'columns' =>
        [
            'id',
            'code',
            'description'
        ]
]); ?>

<? $pjax::end(); ?>

