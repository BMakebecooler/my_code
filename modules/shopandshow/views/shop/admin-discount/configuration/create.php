<?php

use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use modules\shopandshow\models\shop\shopdiscount\ConfigurationValue;
use modules\shopandshow\models\shop\shopdiscount\Configuration;
use modules\shopandshow\models\shop\shopdiscount\Entity;
use \yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model modules\shopandshow\models\shop\ShopDiscount */
/* @var $form ActiveForm */
/* @var $configuration Configuration */

$entities = Entity::find()->all();
// TODO убрать этот фильтр когда будут реализованы все классы условий
$entities = array_filter($entities, function($entity) {
    $className = ConfigurationValue::getClassNameByEntityClass($entity['class']);
    return class_exists($className);
});
$entity_list = ArrayHelper::map($entities,'id','name');
?>

<div class="configuration-form">
    <?= $form->fieldSelect($configuration, 'shop_discount_entity_id', $entity_list); ?>

    <?php foreach ( $entities as $entity): ?>
        <?php
        $className = ConfigurationValue::getClassNameByEntityClass($entity['class']);
        if(class_exists($className)) {
            $viewName = ConfigurationValue::getViewNameByEntityClass($entity['class']);
            echo $this->render(
                $viewName,
                ['form' => $form, 'model' => new $className, 'entity' => $entity]
            );
            continue;
        }
        else {
            echo '<div id="entity-id'.$entity->id.'" class="entity-param alert alert-warning" style="display: none;" >Пока не готово</div>';
        }
        ?>
    <?php endforeach; ?>
</div>

<?php

$this->registerJs('
    $(document).ready(function () {
        var entities = $(".configuration-form");
        var params = entities.find("div.entity-param");

        $(entities).change(function () {
            var id = $(this).find(":selected").val();
            var selected_div = $("#entity-id"+id).toArray()[0];
            
            $(params).toArray().forEach(function (div) {
                if(div == selected_div){
                    $(div).css(\'display\',\'block\');
                } else {
                    $(div).css(\'display\',\'none\');
                }
            });
        });
        
        entities.trigger(\'change\');
    })');
?>