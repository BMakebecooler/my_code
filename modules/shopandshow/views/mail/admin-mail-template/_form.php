<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

/* @var $this yii\web\View */

use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

$CmsTreeData = \common\models\Tree::find()
    ->active()
    ->where(['tree_type_id' => CATALOG_TREE_TYPE_ID])
    ->andWhere('level > 1')
    ->select(['id', 'name', 'level'])
    ->orderBy('dir')
    ->asArray()
    ->all();


$CmsTree = \yii\helpers\ArrayHelper::merge(['' => '[Определять автоматически из раздела товара ЦТС]', '-1' => '[Не выводить товары в рассылке]'],
    \yii\helpers\ArrayHelper::map($CmsTreeData, 'id', function($row) {return str_repeat('-', ($row['level']-2)*2) . ' ' . $row['name'];})
);

?>

<?php $form = ActiveForm::begin(); ?>

    <?= $form->fieldSelect($model, 'template', $model->getTemplates()); ?>
    <?= $form->field($model, 'tree_id')->widget(\kartik\select2\Select2::classname(), [
        'options' => ['multiple' => false],
        'data' => $CmsTree,
        'showToggleAll' => false,
        'pluginOptions' => [
            'maximumInputLength' => 10,
            'allowClear' => true,
        ],
    ]); ?>
    <?= $form->field($model, 'name')->textInput(); ?>
    <?= $form->field($model, 'from')->textInput(); ?>
    <?= $form->fieldCheckboxBoolean($model, 'active'); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
