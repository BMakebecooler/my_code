<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 27.05.2015
 */
use common\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $contentType \skeeks\cms\models\CmsContentType */
/* @var $model \skeeks\cms\shop\cmsWidgets\filters\ShopProductFiltersWidget */

$result = [];
if ($contentTypes = \skeeks\cms\models\CmsContentType::find()->all()) {
    foreach ($contentTypes as $contentType) {
        $result[$contentType->name] = \yii\helpers\ArrayHelper::map($contentType->cmsContents, 'id', 'name');
    }
}
?>
<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Showing')); ?>
<?= $form->field($model, 'viewFile')->textInput(); ?>
<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\Yii::t('app', 'Data source')); ?>
<?= $form->fieldSelect($model, 'content_id', $result); ?>

<?

/*$form->field($model, 'searchModelAttributes')->dropDownList([
    'image' => \Yii::t('skeeks/shop/app', 'Filter by photo'),
    'hasQuantity' => \Yii::t('skeeks/shop/app', 'Filter by availability')
], [
    'multiple' => true,
    'size' => 4
]); */

?>

<? if ($model->cmsContent) : ?>
    <?
    $realatedProperties = $model->cmsContent->getCmsContentProperties()
        //->andWhere('property_type =:type', [':type' => \skeeks\cms\relatedProperties\PropertyType::CODE_ELEMENT]) //При желании убрать, но надо будет переписать запрос выборки поля
        ->all();

    //echo $form->fieldSelectMulti($model, 'realatedProperties', \yii\helpers\ArrayHelper::map($realatedProperties, 'code', 'name'));
    ?>

    <?= $form->fieldSelectMulti($model, 'realatedProperties', ArrayHelper::map($realatedProperties, 'code',  function($model) {
        return $model['name'].' ('.$model['code']. ')';
    }), [
        'empty' => 'Выберите атрибуты'
    ]);
    ?>

    <? if ($model->shopContent && $model->shopContent->offerContent) : ?>
        <? //$form->fieldSelectMulti($model, 'offerRelatedProperties', \yii\helpers\ArrayHelper::map($model->shopContent->offerContent->cmsContentProperties, 'code', 'name')); ?>
    <? endif; ?>

<? else: ?>
    Дополнительные свойства появятся после сохранения настроек
<? endif; ?>

<?= $form->fieldSetEnd(); ?>



