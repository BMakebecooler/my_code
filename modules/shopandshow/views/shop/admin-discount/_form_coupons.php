<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use modules\shopandshow\models\shop\ShopDiscount;
use modules\shopandshow\models\shop\ShopDiscountCoupon;
use yii\data\ActiveDataProvider;
use skeeks\cms\modules\admin\components\UrlRule;

/* @var $this yii\web\View */
/* @var $model modules\shopandshow\models\shop\ShopDiscount */
/* @var $form ActiveForm */

$dataProvider = new ActiveDataProvider([
    'query' => ShopDiscountCoupon::find()->where(['shop_discount_id' => $model->id]),
    'pagination' => [
        'pageSize' => 20,
    ],
]);
?>

<?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
    'enabledCheckbox' => false,
    'dataProvider'      => $dataProvider,
    'columns'           =>
        [
            'id',
            [
                'label' => 'Купон',
                'format' => 'raw',
                'value' => function (ShopDiscountCoupon $data) use ($model) {
                    return \yii\helpers\BaseHtml::a(
                        $data->coupon,
                        \yii\helpers\Url::to(['shop/admin-discount-coupon/update?pk='.$data->id, UrlRule::ADMIN_PARAM_NAME => UrlRule::ADMIN_PARAM_VALUE])
                    );
                }
            ],

            [
                'attribute'     => 'Использование',
                'class'         => \yii\grid\DataColumn::className(),
                'value' => function(modules\shopandshow\models\shop\ShopDiscountCoupon $shopDiscountCoupon)
                {
                    return $shopDiscountCoupon->use_count.' / '.$shopDiscountCoupon->max_use;
                },
            ],

            [
                'attribute'     => 'is_active',
                'class'         => \skeeks\cms\grid\BooleanColumn::className(),
            ],

            [
                'attribute'     => 'active_from',
                'class'         => \skeeks\cms\grid\DateTimeColumnData::className(),
            ],

            [
                'attribute'     => 'active_to',
                'class'         => \skeeks\cms\grid\DateTimeColumnData::className(),
            ],

            [
                'class' => \skeeks\cms\grid\CreatedAtColumn::className()
            ],
        ]
]); ?>