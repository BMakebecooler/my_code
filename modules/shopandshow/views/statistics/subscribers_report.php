<?php
/* @var $this yii\web\View */

/** @var $model \modules\shopandshow\models\statistic\Statistics */
/** @var $dataProvider \yii\data\ArrayDataProvider */

use modules\shopandshow\models\users\UserEmail;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;
use common\helpers\ArrayHelper;

$form = ActiveForm::begin([
    'action'    => Url::to(['/~sx/shopandshow/statistics/subscribers-report']),
    'enableAjaxValidation' => false,
    'method'    => 'post'
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

$dataProvider = $model->getSubscribersReportDataProvider();
$models = $dataProvider->getModels();

$subscribersTotalNum            = (!empty($models['phone']['num']) ? $models['phone']['num'] : 0) +
                                    (!empty($models['site_all']['num']) ? $models['site_all']['num'] : 0);

$subscribersValidSiteTotalNum   = (!empty($models['phone']['valid_num_site']) ? $models['phone']['valid_num_site'] : 0) +
                                    (!empty($models['site_all']['valid_num_site']) ? $models['site_all']['valid_num_site'] : 0);

$subscribersValidGrTotalNum     = (!empty($models['phone']['valid_num_gr']) ? $models['phone']['valid_num_gr'] : 0) +
                                    (!empty($models['site_all']['valid_num_gr']) ? $models['site_all']['valid_num_gr'] : 0);

$usersTotalNum                  = (!empty($models['phone']['users']) ? $models['phone']['users'] : 0) +
                                    (!empty($models['site_all']['users']) ? $models['site_all']['users'] : 0);

$usersTotalNumOld               = (!empty($models['phone']['users_old']) ? $models['phone']['users_old'] : 0) +
    (!empty($models['site_all']['users_old']) ? $models['site_all']['users_old'] : 0);

echo '<hr>';

echo \skeeks\cms\modules\admin\widgets\GridView::widget([
    'dataProvider'  => $dataProvider,
    'showFooter' => true,
    'footerRowOptions' => ['style' => 'font-weight:bold;', 'class' => 'bg-info'],
    'layout'        => '{items}',
    'columns'       => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'label' => 'Источник',
            'attribute' => 'source',
            'value' => function($row){
                return UserEmail::getSourceLabel($row['source']);
            },
        ],
        [
            'label' => 'Источник детально',
            'attribute' => 'source_detail',
            'value' => function($row){
                return UserEmail::getSourceDetailLabel($row['source_detail']);
            },
        ],
        /*
        [
            'label'     => "Пользователей всего (без типизации)",
            'attribute' => 'users_old',
            'footer'    => $usersTotalNumOld
        ],
        */
        [
            'label'     => "Пользователей всего",
            'attribute' => 'users',
            'footer'    => $usersTotalNum
        ],
        [
            'label'     => "Email'ов всего",
            'attribute' => 'num',
            'footer'    => $subscribersTotalNum
        ],
        [
            'label'     => "Email'ов валидных (сайт)",
            'attribute' => 'valid_num_site',
            'footer'    => $subscribersValidSiteTotalNum
        ],
        /*
        [
            'label'     => "Email'ов валидных (getResponse)",
            'attribute' => 'valid_num_gr',
            'footer'    => $subscribersValidGrTotalNum
        ],
        */
    ]
]);

ActiveForm::end();

?>