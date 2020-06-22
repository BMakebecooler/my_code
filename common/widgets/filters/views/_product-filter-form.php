<?php
/* @var $this yii\web\View */
/* @var $contentType \skeeks\cms\models\CmsContentType */
/* @var $model common\widgets\filters\ProductFiltersWidget */

use common\helpers\ArrayHelper;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

?>
<?php $form = ActiveForm::begin(); ?>

<?= $form->fieldSet('Настройка полей'); ?>

<?= $form->fieldSelectMulti($model, 'filteredProperties', ArrayHelper::map($model->getFilteredProperties(), 'id', function ($model) {

    $labelTypeProperty = ($model['content_id'] == PRODUCT_CONTENT_ID) ? 'Лот' : 'Предложение';

    return sprintf('[%s] %s (Код: %s, Ид: %s)', $labelTypeProperty, $model['name'], $model['code'], $model['id']);
}), [
    'empty' => 'Выберите атрибуты'
]);
?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsStandart($model) ?>
<?php ActiveForm::end(); ?>