<?php
use common\helpers\User;
use common\models\cmsContent\ContentElementFaq;
use modules\shopandshow\models\questions\QuestionEmail;
use skeeks\cms\models\CmsTree;
use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model QuestionEmail */


?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'group')->dropDownList(
     QuestionEmail::getGroupList()
); ?>

<?= $form->field($model, 'type')->dropDownList(
    QuestionEmail::getTypeList()
); ?>

<?= $form->field($model, 'tree_id')->widget(
    \skeeks\cms\widgets\formInputs\selectTree\SelectTreeInputWidget::class,
    [
        'isAllowNodeSelectCallback' => function (CmsTree $node) {
            return $node->tree_type_id == CATALOG_TREE_TYPE_ID;
        }
    ]
); ?>

<?= $form->field($model, 'fio')->textInput(); ?>
<?= $form->field($model, 'email')->textInput(); ?>


<?= $form->buttonsCreateOrUpdate($model); ?>

<?php ActiveForm::end(); ?>
