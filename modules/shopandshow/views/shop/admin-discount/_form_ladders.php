<?php
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use modules\shopandshow\models\shop\SsShopDiscountLogic;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model modules\shopandshow\models\shop\ShopDiscount */
/* @var $form ActiveForm */

/** @var SsShopDiscountLogic[] $shopDiscountLogics */
$shopDiscountLogics = $model->getShopDiscountLogics()->all();

$shopDiscountLogicNew = new SsShopDiscountLogic(['shop_discount_id' => $model->id]);
$shopDiscountLogicNew->initDefaultValues();
?>
<div class="shop-discount-ladder">
    <?php foreach($shopDiscountLogics as $shopDiscountLogic): ?>
    <div class="ladder-element row">
        <div class="col-sm-12 col-md-6">
            <?= $form->fieldSelect($shopDiscountLogic, '['.$shopDiscountLogic->id.']logic_type', $shopDiscountLogic::getLogicTypes()); ?>
        </div>
        <div class="col-sm-12 col-md-6">
            <?= $form->field($shopDiscountLogic, '['.$shopDiscountLogic->id.']value')->textInput(); ?>
        </div>
        <div class="col-sm-12 col-md-6">
            <?= $form->fieldSelect($shopDiscountLogic, '['.$shopDiscountLogic->id.']discount_type', $shopDiscountLogic::getDiscountTypes()); ?>
        </div>
        <div class="col-sm-12 col-md-6">
            <?= $form->field($shopDiscountLogic, '['.$shopDiscountLogic->id.']discount_value')->textInput(); ?>
        </div>
        <div class="col-sm-12">
            <label>
                <input type="checkbox" name="<?=$shopDiscountLogic->formName();?>[<?=$shopDiscountLogic->id;?>][flag_delete]" value="1"> Удалить
            </label>
        </div>
    </div>
    <?php endforeach; ?>
    <button type="button" class="btn btn-success" id="shop-discount-logic-add">Добавить скидку</button>
    <input type="hidden" name="ShopDiscountLogicCreate" id="ShopDiscountLogicCreate" value="0">
    <div class="ladder-element row" id="shop-discount-logic-new" style="display:none;">
        <div class="col-sm-12 col-md-6">
            <?= $form->fieldSelect($shopDiscountLogicNew, '[new]logic_type', $shopDiscountLogicNew::getLogicTypes()); ?>
        </div>
        <div class="col-sm-12 col-md-6">
            <?= $form->field($shopDiscountLogicNew, '[new]value')->textInput(); ?>
        </div>
        <div class="col-sm-12 col-md-6">
            <?= $form->fieldSelect($shopDiscountLogicNew, '[new]discount_type', $shopDiscountLogicNew::getDiscountTypes()); ?>
        </div>
        <div class="col-sm-12 col-md-6">
            <?= $form->field($shopDiscountLogicNew, '[new]discount_value')->textInput(); ?>
        </div>
    </div>
</div>

<?= $this->registerCss('
    .shop-discount-ladder .ladder-element {
        border: 1px solid #ddd;
        border-radius: 4px;
        margin: 5px;
    }
'); ?>
<?= $this->registerJs('
    $(document).ready(function () {
        $("#shop-discount-logic-add").click(function() {
            $("#shop-discount-logic-new").show();
            $("#ShopDiscountLogicCreate").val(1);
            $(this).hide();
        });
    })'); ?>
