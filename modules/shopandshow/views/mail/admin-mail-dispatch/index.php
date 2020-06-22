<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 02.06.2015
 */

use modules\shopandshow\models\mail\MailDispatch;

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
        'settingsData' =>
        [
            'order' => SORT_DESC,
            'orderBy' => "created_at",
        ],

        'columns'           =>
        [
            'id',
            'subject',
            [
                'filter'        => (array) \yii\helpers\ArrayHelper::map(\modules\shopandshow\models\mail\MailTemplate::find()->all(), 'id', 'name'),
                'attribute'     => 'mail_template_id',
                'value'         => function (MailDispatch $mailDispatch)
                {
                    return $mailDispatch->mailTemplate->template;
                },
            ],
            [
                'filter'        => MailDispatch::getStatusList(),
                'attribute'     => 'status',
                'value'         => function (MailDispatch $mailDispatch)
                {

                    return $mailDispatch::getStatusList()[$mailDispatch->status];
                },
            ],
            'from',
            [
                'attribute' => 'to',
                'format' => 'raw',
                'value' => function (MailDispatch $mailDispatch) {
                    return str_replace(',', '<br>', $mailDispatch->to);
                }
            ],
            [
                'class' => \skeeks\cms\grid\CreatedAtColumn::className(),
                'label' => 'Дата отправки'
            ],

        ]
    ]); ?>

<? $pjax::end(); ?>
