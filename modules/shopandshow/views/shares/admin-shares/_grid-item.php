<?php
use modules\shopandshow\models\shares\SsShareSchedule;

$prefix = $model->isNewRecord ? '[new]' : "[{$model->id}]";

/* @var $model SsShareSchedule */
?>
<div class="block well row"
     id="block_<?= $model->isNewRecord ? 'new' : $model->id ?>"
     data-block-num-by-type="<?= !empty($blockNumByType) ? $blockNumByType : 0; ?>"
     data-id="<?= $model->id; ?>"
>
    <div class="block-admin col-xs-4">
        <?php if (!$model->isNewRecord): ?>
        <div class="form-group id">
            <label class="control-label">id блока: <span style="color: green">block_<?= $model->id; ?></span></label>
        </div>
        <?php endif; ?>

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
                        SsShareSchedule::getBlockList()
                    )
            ); ?>
        </div>

        <div class="form-group block_category">
            <?= $form->field($model, "{$prefix}tree_id")->dropDownList(
                \common\helpers\ArrayHelper::merge(
                    [0 => ' --- Нет ---'],
                    \modules\shopandshow\models\mediaplan\AirBlock::getAvailSections()
                )
            ); ?>
        </div>

        <div class="form-group block_position">
            <?= $form->field($model, "{$prefix}block_position"); ?>
        </div>
        <div class="form-group block_name">
            <?= $form->field($model, "{$prefix}name"); ?>
        </div>
        <div class="form-group block_description">
            <?= $form->field($model, "{$prefix}description"); ?>
        </div>
    </div>
    <div class="block-preview col-xs-6 well">

    </div>

    <?php if($model->isNewRecord): ?>
            <div class="col-xs-6">
                <?= \yii\helpers\Html::submitButton("Добавить", [
                    'class' => 'btn btn-primary',
                    'onclick' => "return sx.CmsActiveFormButtons.go('apply');",
                ]); ?>
            </div>

    <?php endif; ?>
</div>


