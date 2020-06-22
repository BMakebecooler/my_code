<?php
/* @var $message string */
/* @var $this yii\web\View */
/* @var $searchModel \skeeks\cms\models\Search */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>

<? $pjax = \skeeks\cms\modules\admin\widgets\Pjax::begin(); ?>

    <br><br>

<?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'pjax' => $pjax,
//    'adminController' => \Yii::$app->controller,
    'enabledCheckbox' => false,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'label' => "ЛОТ",
            'attribute' => 'lot_id',
            'format' => 'raw',
            'value' => function ($data) {

                $product = \common\lists\Contents::getContentElementById($data["product_id"]);

                return 'Лот - ' . $data['lot_id'] . ' ' . \yii\bootstrap\Html::a(
                        'Открыть товар',
                        $product->absoluteUrl,
                        ['target' => '_blank', 'data-pjax' => 0]
                    );
            }
        ],
        [
            'label' => "Остаток",
            'attribute' => 'quantity',
            'value' => function ($data) {
                return $data["quantity"];
            }
        ],
        [
            'label' => "Кол-во продаж",
            'attribute' => 'count_sale',
            'value' => function ($data) {
                return $data["count_sale"];
            }
        ],
    ],
]);
?>

<? $pjax::end(); ?>

<?php
$this->registerCss('
    tr.active_share td {
        background-color: #dff0d8 !important;
    }
');
?>