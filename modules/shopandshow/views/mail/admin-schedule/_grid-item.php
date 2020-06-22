<?php
use modules\shopandshow\models\shares\SsMailSchedule;

$prefix = $model->isNewRecord ? '[new]' : "[{$model->id}]";

/* @var $model SsMailSchedule */
?>
<div class="block well row" id="block_<?= $model->isNewRecord ? 'new' : $model->id ?>">
    <div class="block-admin col-xs-4">
        <div class="form-group begin_datetime">
            <?= $form->field($model, "{$prefix}begin_datetime")->widget(
                \kartik\datecontrol\DateControl::class,
                [
                    'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
                    'displayFormat' => 'php:Y-m-d H:i',
                ]
            ); ?>
        </div>

        <div class="form-group end_datetime">
            <?= $form->field($model, "{$prefix}end_datetime")->widget(
                \kartik\datecontrol\DateControl::class,
                [
                    'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
                    'displayFormat' => 'php:Y-m-d H:i',
                ]
            ); ?>
        </div>

        <div class="form-group block_type">
            <?= $form->field($model, "{$prefix}block_type")->dropDownList(
                    \common\helpers\ArrayHelper::merge(
                        [0 => ' --- Удалить ---'],
                        array_map(function($item) {return $item['title'];}, SsMailSchedule::getBlockList())
                    )
            ); ?>
        </div>

        <div class="form-group block_position">
            <?= $form->field($model, "{$prefix}block_position"); ?>
        </div>
    </div>
    <div class="block-preview col-xs-8 well" style="width: 740px;">
        <?php if (!$model->isNewRecord): ?>
            <?= $model->getWidget(); ?>
        <?php endif; ?>
    </div>

    <?php if ($model->isNewRecord): ?>
            <div class="form-group clearfix">
                <?= \yii\helpers\Html::submitButton("Добавить", [
                    'class' => 'btn btn-primary',
                    'onclick' => "return sx.CmsActiveFormButtons.go('apply');",
                ]); ?>
            </div>
    <?php endif; ?>
</div>


