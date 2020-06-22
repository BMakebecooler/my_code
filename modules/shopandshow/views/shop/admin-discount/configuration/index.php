<?php

use skeeks\cms\helpers\UrlHelper;
use yii\helpers\Html;
use yii\grid\GridView;
use modules\shopandshow\models\shop\shopdiscount\ConfigurationValue;
use modules\shopandshow\models\shop\shopdiscount\Configuration;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model modules\shopandshow\models\shop\ShopDiscount */
/* @var $form ActiveForm */

?>

<div class="configuration-index">
    <?php if(!$model->isNewRecord): ?>
        <h1><?= Html::encode("Условия") ?></h1>
        <?= GridView::widget([
            'dataProvider' => $model->getConfigDataProvider(),
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'Условие',
                    'value' => 'entity.name'
                ],
                [
                    'attribute' => 'Значение',
                    'format' => 'html',
                    'value' => function (Configuration $data) {
                        $values = $data->getValues()->all();
                        return array_reduce($values, function($result, ConfigurationValue $value) {
                            $result .= $value->formatOutput($value->getLinkedValue()).'<br>';
                            return $result;
                        }, '');
                    },
                ],

                [
                    'class' => 'yii\grid\CheckboxColumn',
                    //'controller' => '/~sx/shopandshow/shop/shopdiscount/configuration',
                    //'template' => '{delete}',
                    'contentOptions' => ['class' => 'text-center'],
                    'name' => 'Configuration[delete]',
                    'header' => 'Удалить',

                ],
            ],
        ]); ?>


        <?
        $url = \skeeks\cms\helpers\UrlHelper::construct(['/shopandshow/shop/admin-discount/export-products'])
            ->enableAdmin()
            ->addData(['id' => $model->id])
            ->normalizeCurrentRoute()->toString();
        ?>
        <a target="_blank" data-pjax="0" class="btn btn-warning pull-right" href="<?= $url ?>">Экспортировать</a>

    <?php endif; ?>
    <h1><?= Html::encode("Добавить") ?></h1>

    <?= $this->render('create', [
        'model' => $model,
        'form'  => $form,
        'configuration' => new Configuration(['shop_discount_id' => $model->id]),
    ]); ?>
</div>