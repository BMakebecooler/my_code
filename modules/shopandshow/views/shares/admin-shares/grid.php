<?php

use modules\shopandshow\models\shares\SsShare;
use modules\shopandshow\models\shares\SsShareSchedule;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $models SsShareSchedule[] */
/* @var $searchDate string */

frontend\assets\v2\common\SlickSlider::register($this);

\Yii::$app->cmsToolbar->initEnabled();
\Yii::$app->cmsToolbar->enabled = true;
\Yii::$app->cmsToolbar->editWidgets = \skeeks\cms\components\Cms::BOOL_Y;
\Yii::$app->cmsToolbar->inited = true;

$url = \skeeks\cms\helpers\UrlHelper::construct(['shares/admin-shares/grid-preview'])->enableAdmin()
    ->normalizeCurrentRoute()->toString();

$gridPreviewUrl = Url::to(sprintf('/?%s=%s', SsShare::BANNER_PREVIEW_KEY, date('Y-m-d H:i:s', $searchDate)));

$this->registerJs(<<<JS
    $('.block-admin .block_type select').on('change', function(data, event) {
        rebuildBlocksNumByType();
        loadPreviewBlocks();
    });

		$('.block-preview').on('change', '.banner-type-select', function(data, event) {
		    var curEl = $(this);
		    var previewBlock = $(data.delegateTarget);
		    
		    var prevVal = curEl.data('prev-val');
		    var newVal = curEl.val();
		    
		    previewBlock.find('select.banner-type-select').each(function() {
		      if( $(this).val() == newVal && $(this).data('prev-val') != prevVal ){
		          $(this).val(prevVal).data('prev-val', prevVal);
		      }
		    });
		    
		    curEl.data('prev-val', newVal).val(newVal);
		});

    $(function() {
        loadPreviewBlocks();
    });
    
    function loadPreviewBlocks(){
        $('.block.row').each(function() {
            var block = $(this).find('.block-admin .block_type select').val();
		        var previewBlock = $(this).find('.block-preview');
		        var blockNumByType = $(this).data('block-num-by-type');
		        var blockId = $(this).data('id');
		        
		        if(block == 0) {
		            previewBlock.empty();
		        }else{
				        previewBlock.html('Загружаем...');
				        previewBlock.load('{$url}', {block: block, searchdate: '{$searchDate}', block_num_by_type: blockNumByType, block_id: blockId});		            
		        }
        });
    }
    
    function rebuildBlocksNumByType() {
        var blocksNumByTypes = {};
				$('.block.row').each(function() {
				    var blockType = $(this).find('.block-admin .block_type select').val();
				    if (blockType != 0){
						    if (!blocksNumByTypes.hasOwnProperty(blockType)){
						        blocksNumByTypes[blockType] = 0;
						    }else{
		                blocksNumByTypes[blockType]++;
						    }
		            var blockNumByType = blocksNumByTypes[blockType];
						    $(this).data('block-num-by-type', blockNumByType);
				    }
				});
    };
JS
);
?>

    <style>
        div#block_new {
            display: none;
        }

        .discount-banner-row.discount-banner-list .banner-item span {
            background: lightblue;
        }

        .discount-banner-row.discount-banner-list .banner-item img[src=''] {
            background: lightblue;
        }

        .block .form-group {margin-bottom:0;}
        .block .form-group label {margin-bottom:0;}
    </style>

<? if ($message): ?>
    <? \yii\bootstrap\Alert::begin([
        'options' => [
            'class' => 'alert-success',
        ]
    ]); ?>
    <?= $message; ?>
    <? \yii\bootstrap\Alert::end(); ?>
<? endif; ?>

<? // Форма поиска баннерных сеток на указанную дату ?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => false]); ?>

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
        <button type="button" class="btn btn-primary glyphicon glyphicon-plus" title="Добавить новый блок"
                onclick="$('#block_new').toggle()"></button>
        <br><br>
        <?= $this->render('_grid-item', ['model' => SsShareSchedule::createNew(), 'form' => $form]); ?>
    </div>

    <div class="h3">Выбранные блоки</div>
<? // Форма редактирования блоков ?>
<?php if ($models): ?>
    <div style="margin-bottom: 20px;">
        <?= \yii\helpers\Html::a("Посмотреть на сайте !!!!",
            $gridPreviewUrl,
            ['class' => 'btn btn-success', 'target' => '_blank']
        ); ?>
    </div>
    <div class="well block-list">
        <?php $blocksNumByTypes = array(); ?>
        <?php foreach ($models as $model): ?>
            <?php
            $blockType = $model->attributes['block_type'];
            if (!isset($blocksNumByTypes[$blockType])) {
                $blocksNumByTypes[$blockType] = 0;

            } else {
                $blocksNumByTypes[$blockType]++;
            }
            $blockNumByType = $blocksNumByTypes[$blockType];
            ?>
            <?= $this->render('_grid-item', ['model' => $model, 'form' => $form, 'blockNumByType' => $blockNumByType]); ?>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="alert alert-warning">На указанную дату ни одной баннерной сетки не добавлено</div>
<?php endif; ?>

    <div class="form-group">
        <?= \yii\helpers\Html::submitButton("Сохранить", [
            'class' => 'btn btn-primary',
            'onclick' => "return sx.CmsActiveFormButtons.go('apply');",
        ]); ?>
    </div>

<?php ActiveForm::end(); ?>