<?php
/* @var $model \common\widgets\shares\blockslider\BannerContentElementWidget */

?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Showing')); ?>

<?= $form->field($model, 'buttonText')->textInput()->hint('Оставьте пустым, чтобы кнопка не выводилась'); ?>
<?= $form->field($model, 'buttonUrl')->textInput()->hint('Оставьте пустым, чтобы ссылка вела на список лотов этой акции'); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet('Тонкие настройки'); ?>
<?= $form->field($model, 'viewFile')->textInput(); ?>
<?= $form->fieldSetEnd(); ?>




