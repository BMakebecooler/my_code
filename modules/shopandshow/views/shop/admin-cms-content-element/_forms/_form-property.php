<?php
/* @var $this yii\web\View */
/* @var $model common\models\cmsContent\CmsContentElement */
/* @var $relatedModel \skeeks\cms\relatedProperties\models\RelatedPropertiesModel */
?>
<?= $form->fieldSet('Свойства'); ?>
<? if ($model->relatedAdminPropertiesModel->properties) : ?>
    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \Yii::t('skeeks/cms', 'Additional properties')
    ]); ?>
    <? foreach ($model->relatedAdminPropertiesModel->properties as $property) : ?>
        <?= $property->renderActiveForm($form) ?>
    <? endforeach; ?>
<? else : ?>
    <?= \Yii::t('skeeks/cms', 'Additional properties are not set') ?>
<? endif; ?>
<?= $form->fieldSetEnd() ?>
