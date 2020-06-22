<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model modules\shopandshow\models\shop\ShopDiscount */
/* @var $form ActiveForm */
?>

<?= $form->field($model, 'typePrices')->checkboxList(\yii\helpers\ArrayHelper::map(
    \skeeks\cms\shop\models\ShopTypePrice::find()->all(), 'id', 'name'
))->hint(\Yii::t('skeeks/shop/app', 'if nothing is selected, it means all')); ?>


<? /* \yii\bootstrap\Alert::begin([
    'options' => [
        'class' => 'alert-warning',
    ],
]); ?>
<?=  \Yii::t('skeeks/shop/app', '<b> Warning! </b> Permissions are stored in real time. Thus, these settings are independent of site or user.'); ?>
<? \yii\bootstrap\Alert::end()?>

<?= \skeeks\cms\rbac\widgets\adminPermissionForRoles\AdminPermissionForRolesWidget::widget([
    'permissionName'            => $model->permissionName,
    'notClosedRoles'            => [],
    'permissionDescription'     => \Yii::t('skeeks/shop/app', 'Groups of users who can benefit from discounted rates').": '{$model->name}'",
    'label'                     => \Yii::t('skeeks/shop/app', 'Groups of users who can benefit from discounted rates'),
]); */?>

