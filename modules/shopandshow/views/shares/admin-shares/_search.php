<?php
/* @var $this yii\web\View */
/* @var $searchModel \skeeks\cms\models\Search */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>

<? $form = \skeeks\cms\modules\admin\widgets\filters\AdminFiltersForm::begin([
        'action' => '/' . \Yii::$app->request->pathInfo,
    ]); ?>

    <?= $form->field($searchModel, 'name')->setVisible(); ?>
    <?= $form->field($searchModel, 'period_from')->setVisible()->widget(
    \kartik\datecontrol\DateControl::class,
    [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        'displayFormat' => 'php:Y-m-d H:i:s',
    ]
); ?>
    <?= $form->field($searchModel, 'period_to')->setVisible()->widget(
    \kartik\datecontrol\DateControl::class,
    [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        'displayFormat' => 'php:Y-m-d H:i:s',
    ]
); ?>

<? $form::end(); ?>

<?
$exportUrl = \skeeks\cms\helpers\UrlHelper::construct(['shares/admin-shares/export'])->enableAdmin()
    ->normalizeCurrentRoute()->toString();
?>
<? $this->registerJs(<<<JS
(function() {
    var exportButton = 
        $('<button>', {type: 'button', class: 'btn btn-default pull-left', style: 'margin-left: 10px;'})
            .append('<i class="glyphicon glyphicon-export"></i> Экспорт в Excel')
            .on('click', function() {
                var origUrl = $('#{$form->id}').attr('action');
                $('#{$form->id}').attr('action', '{$exportUrl}');
                $('#{$form->id}').get(0).submit();
                $('#{$form->id}').attr('action', origUrl);
            });
    
    $('#{$form->id} .form-group-footer .sx-btn-filter-close').after(exportButton);
})();
JS
); ?>
