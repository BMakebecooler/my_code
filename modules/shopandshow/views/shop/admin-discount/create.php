<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 02.06.2015
 */
/* @var $this yii\web\View */
/* @var $model modules\shopandshow\models\shop\ShopDiscount */

$model->initDefaultValues();
?>

<?= $this->render('_form', [
    'model' => $model
]); ?>
