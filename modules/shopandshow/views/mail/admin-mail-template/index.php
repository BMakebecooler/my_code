<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 02.06.2015
 */

use modules\shopandshow\models\mail\MailTemplate;
use skeeks\cms\components\Cms;

/* @var $this yii\web\View */
/* @var $searchModel \skeeks\cms\models\Search */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>

<? $pjax = \skeeks\cms\modules\admin\widgets\Pjax::begin(); ?>

    <?php echo $this->render('_search', [
        'searchModel'   => $searchModel,
        'dataProvider'  => $dataProvider
    ]); ?>

    <?php
    $dailyTemplates = ['SandsCtsGrid'];
    $mailTemplates = MailTemplate::find()
        ->andWhere(['active' => Cms::BOOL_Y])
        ->andWhere(['template' => $dailyTemplates])
        ->all();
    if (sizeof($mailTemplates) != 1): ?>
        <? \yii\bootstrap\Alert::begin([
            'options' => [
              'class' => 'alert-warning',
          ],
        ]); ?>

        <b> Внимание! </b> Для осуществления автоматических рассылок должен быть активен ровно 1 шаблон из: <?= join(', ', $dailyTemplates); ?>

        <? \yii\bootstrap\Alert::end()?>

    <? endif; ?>

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
            'name',
            [
                'attribute'     => 'active',
                'class'         => \skeeks\cms\grid\BooleanColumn::className(),
            ],
            [
                'attribute'     => 'tree_id',
                'class'         => \yii\grid\DataColumn::className(),
                'format'        => 'raw',
                'value' => function(MailTemplate $mailTemplate)
                {
                    if ($mailTemplate->tree_id > 0) return $mailTemplate->tree->name;
                    elseif ($mailTemplate->tree_id == -1) return '- Не выводить товары в рассылке';

                    return '* Выберется из категории товара ЦТС';
                },
            ],
            'template',
            'from',
            'check' => [
                'format' => 'raw',
                'label' => 'Проверить',
                'value' => function(MailTemplate $mailTemplate) {
                    $checkUrl = \skeeks\cms\helpers\UrlHelper::construct("/" . $this->context->id . '/' . $this->context->action->id)->enableAdmin()->setRoute('check')->normalizeCurrentRoute()->toString();
                    return \yii\bootstrap\Html::a('Проверить', [$checkUrl, 'pk' => $mailTemplate->id]);
                }
            ]
        ]
    ]); ?>

<? $pjax::end(); ?>
