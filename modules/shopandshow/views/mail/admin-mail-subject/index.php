<?php

use modules\shopandshow\models\mail\MailSubject;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\modules\admin\widgets\GridViewStandart;
use skeeks\cms\modules\admin\widgets\Pjax;
use yii\base\ErrorException;
use yii\grid\DataColumn;

/* @var $this yii\web\View */
/* @var $searchModel \skeeks\cms\models\Search */
/* @var $dataProvider yii\data\ActiveDataProvider */

$pjax = Pjax::begin();


echo $this->render('_search', [
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider
]);



try {
    echo GridViewStandart::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax' => $pjax,
        'adminController' => \Yii::$app->controller,
        'settingsData' =>
            [
                'order' => SORT_DESC,
                'orderBy' => "created_at",
            ],

        'columns' =>
            [
                'id',
                'name',
                'subject',
                [
                    'attribute' => 'active',
                    'class' => BooleanColumn::className(),
                ],
                [
                    'attribute' => 'template_id',
                    'class' => DataColumn::className(),
                    'format' => 'raw',
                    'value' => function (MailSubject $mailTopic) {
                        return $mailTopic->template->template;
                    },
                ],
                [
                    'attribute' => 'begin_datetime',
                    'class' => DateTimeColumnData::className(),
                ],
                [
                    'attribute' => 'end_datetime',
                    'class' => DateTimeColumnData::className(),
                ],
            ]
    ]);
} catch (ErrorException $e) {
    echo 'ErrorException: ';
    echo '<hr/><br/><br/>' . $e->getMessage() . '<br/><hr/>';
    echo '<br/><pre>' . $e->getTraceAsString() . '</pre><br/><hr/>';
    exit('<br/>Exit in file: ' . __FILE__ . ' on line ' . __LINE__);
} catch (Exception $e) {
    echo get_class($e);
    echo '<hr/><br/><br/>' . $e->getMessage() . '<br/><hr/>';
    echo '<br/><pre>' . $e->getTraceAsString() . '</pre><br/><hr/>';
    exit('<br/>Exit in file: ' . __FILE__ . ' on line ' . __LINE__);
}


?>

<? $pjax::end(); ?>
