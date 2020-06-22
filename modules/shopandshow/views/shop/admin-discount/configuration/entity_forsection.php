<?php

use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use kartik\select2\Select2;
use skeeks\cms\models\CmsTree;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model modules\shopandshow\models\shop\shopdiscount\ConfigurationValue */
/* @var $form ActiveForm */
/* @var $entity modules\shopandshow\models\shop\shopdiscount\Entity */

$CmsTreeData = CmsTree::find()
    ->active()
    ->where(['tree_type_id' => CATALOG_TREE_TYPE_ID])
    ->andWhere('level > 1')
    ->select(['id', 'name', 'level'])
    ->orderBy('dir')
    ->asArray()
    ->all();

$CmsTree = \yii\helpers\ArrayHelper::map($CmsTreeData, 'id', function($row) {return str_repeat('-', ($row['level']-2)*2) . ' ' . $row['name'];});

?>

<div id="entity-id<?=$entity->id?>" class="entity-param" style="display: none;">
    <?= $form->field($model, 'value')->widget(Select2::classname(), [
        'options' => ['multiple' => true],
        'data' => $CmsTree,
        'pluginOptions' => [
            'maximumInputLength' => 10,
            'allowClear' => true
        ],
    ]); ?>
</div>
