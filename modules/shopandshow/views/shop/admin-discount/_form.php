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
?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Main')); ?>
    <?= $this->render('_form_main', [
        'model' => $model,
        'form'  => $form
    ]); ?>
<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Conditions')); ?>
    <?= $this->render('_form_conditions', [
        'model' => $model,
        'form'  => $form
    ]); ?>
<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Limitations')); ?>
    <?= $this->render('_form_limitations', [
        'model' => $model,
        'form'  => $form
    ]); ?>
<?= $form->fieldSetEnd(); ?>


<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Coupons')); ?>
    <?= $this->render('_form_coupons', [
        'model' => $model,
        'form'  => $form
    ]); ?>
<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>

<?php ActiveForm::end(); ?>
