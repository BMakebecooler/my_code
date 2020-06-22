<?php
use modules\shopandshow\models\shares\SsMailSchedule;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $models SsMailSchedule[] */
/* @var $message string */
/* @var $searchDate string */

\Yii::$app->cmsToolbar->initEnabled();
\Yii::$app->cmsToolbar->enabled = true;
\Yii::$app->cmsToolbar->editWidgets = \skeeks\cms\components\Cms::BOOL_Y;
\Yii::$app->cmsToolbar->inited = true;

$url = \skeeks\cms\helpers\UrlHelper::construct(['mail/admin-schedule/grid-preview'])->enableAdmin()
    ->normalizeCurrentRoute()->toString();

$this->registerJs(<<<JS
    $('.block-admin .block_type select').on('change', function(data, event) {
        var block = $(this).val();
        var previewBlock = $(this).closest('.block').find('.block-preview');
        
        if(block == 0) {
            previewBlock.empty();
            return false;
        }
        
        previewBlock.html('Загружаем...');
        previewBlock.load('{$url}', {block: block, searchdate: '{$searchDate}'});
    });
JS
);
?>

<style>
    div#block_new {display: none;}
    .discount-banner-row.discount-banner-list .banner-item span {
        background: lightblue;
    }
</style>

<? // Форма поиска баннерных сеток на указанную дату ?>
<?php $form = ActiveForm::begin(['enableAjaxValidation'=>false]); ?>

<? if ($message): ?>
    <? \yii\bootstrap\Alert::begin([
        'options' => [
            'class' => 'alert-success',
        ]
    ]); ?>
    <?= $message; ?>
    <? \yii\bootstrap\Alert::end(); ?>
<? endif; ?>

<div class="alert alert-info">
    <div class="form-group">
        <label class="control-label" for="searchdate">Дата активности:</label>
        <?= \kartik\datecontrol\DateControl::widget([
                'name' => 'searchdate',
                'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
                'displayFormat' => 'php:Y-m-d H:i',
                'value' => $searchDate
            ]); ?>
    </div>
    <div class="form-group">
        <?= \yii\helpers\Html::submitButton("Показать", [
            'class' => 'btn btn-primary',
            'onclick' => "return sx.CmsActiveFormButtons.go('apply');",
        ]); ?>
    </div>
</div>

<div class="h3">Добавление блока</div>
<div class="well">
    <button type="button" class="btn btn-primary glyphicon glyphicon-plus" title="Добавить новый блок" onclick="$('#block_new').toggle()"></button>
    <br><br>
    <?= $this->render('_grid-item', ['model' => SsMailSchedule::createNew(), 'form' => $form]); ?>
</div>

<div class="h3">Выбранные блоки</div>
<? // Форма редактирования блоков ?>
<?php if($models): ?>
    <div class="well block-list">
        <?php foreach ($models as $model): ?>
            <?= $this->render('_grid-item', ['model' => $model, 'form' => $form]); ?>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="alert alert-warning">На указанную дату ни одного блока не добавлено</div>
<?php endif; ?>

<div class="form-group">
    <?= \yii\helpers\Html::submitButton("Сохранить", [
        'class' => 'btn btn-primary',
        'onclick' => "return sx.CmsActiveFormButtons.go('apply');",
    ]); ?>
</div>

<?php ActiveForm::end(); ?>