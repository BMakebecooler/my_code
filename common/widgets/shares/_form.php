<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 27.05.2015
 */
use common\helpers\ArrayHelper;

/* @var $contentType \skeeks\cms\models\CmsContentType */
/* @var $model \common\widgets\shares\ShareWidget */

?>
<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Showing')); ?>

<?= $form->fieldCheckboxBoolean($model, 'is_active'); ?>
<?= $form->field($model, 'viewFile')->textInput(); ?>
<?= $form->fieldSelect($model, 'type', ArrayHelper::map(\Yii::$app->shares->getTypesBanners(), 'XML_ID',  function($model) {
    return $model['VALUE'].' ('.$model['XML_ID']. ')';
}), [
    'empty' => 'Выберите тип баннера'
]);
?>
<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet('Тонкие настройки'); ?>
<?= $form->fieldSetEnd(); ?>




