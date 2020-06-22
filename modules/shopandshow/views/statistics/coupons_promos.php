<?php
/* @var $this yii\web\View */
/* @var $model modules\shopandshow\models\shop\ShopDiscount */
/* @var $form ActiveForm */
/* @var $dataProvider yii\data\ActiveDataProvider */

use modules\shopandshow\models\statistic\Statistics;
use yii\widgets\ActiveForm;

//var_dump($dataProvider);
//die();

$dataProvider->setSort([
    'attributes' => [
        'id' => [
            'asc' => ['id' => SORT_ASC],
            'desc' => ['id' => SORT_DESC],
            'default' => SORT_DESC
        ],
        'active_from' => [
            'asc' => ['active_from' => SORT_ASC, 'id' => SORT_ASC],
            'desc' => ['active_from' => SORT_DESC, 'id' => SORT_DESC],
            'default' => SORT_DESC
        ],
        'active_to' => [
            'asc' => ['active_to' => SORT_ASC, 'id' => SORT_ASC],
            'desc' => ['active_to' => SORT_DESC, 'id' => SORT_DESC],
            'default' => SORT_DESC
        ],
        'name' => [
            'asc' => ['name' => SORT_ASC, 'id' => SORT_ASC],
            'desc' => ['name' => SORT_DESC, 'id' => SORT_DESC],
            'default' => SORT_DESC
        ],
        'code' => [
            'asc' => ['code' => SORT_ASC, 'id' => SORT_ASC],
            'desc' => ['code' => SORT_DESC, 'id' => SORT_DESC],
            'default' => SORT_DESC
        ],
        'coupons_num' => [
            'asc' => ['coupons_num' => SORT_ASC, 'id' => SORT_ASC],
            'desc' => ['coupons_num' => SORT_DESC, 'id' => SORT_DESC],
            'default' => SORT_DESC
        ],
        'orders_num' => [
            'asc' => ['orders_num' => SORT_ASC, 'id' => SORT_ASC],
            'desc' => ['orders_num' => SORT_DESC, 'id' => SORT_DESC],
            'default' => SORT_DESC
        ],
        'orders_price' => [
            'asc' => ['orders_price' => SORT_ASC, 'id' => SORT_ASC],
            'desc' => ['orders_price' => SORT_DESC, 'id' => SORT_DESC],
            'default' => SORT_DESC
        ],
    ],
    'defaultOrder' => [
        'active_from' => SORT_DESC
    ]
]);

echo \skeeks\cms\modules\admin\widgets\GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => \yii\grid\SerialColumn::class,
        ],
        [
            'label' => "Акция",
            'attribute' => 'name',
        ],
        [
            'label' => "Код",
            'attribute' => 'code'
        ],
        'active_from:datetime:Активно С',
        'active_to:datetime:Активно ПО',
        [
            'label' => "Кол-во купонов",
            'attribute' => 'coupons_num',
        ],
        [
            'label' => "Купоны",
            'attribute' => 'coupons',
            'value' => function ($row) {
                return strlen($row['coupons']) > 50 ? substr($row['coupons'], 0, 50) . '...' : $row['coupons'];
            },
        ],
        [
            'label' => "Заказов",
            'attribute' => 'orders_num',
        ],
        [
            'label' => "Сумма заказов",
            'attribute' => 'orders_price',
            'value' => function ($row) {
                return \Yii::$app->formatter->asDecimal(round($row['orders_price']));
            },
        ],
    ]
]);

?>