<?php

namespace modules\shopandshow\models\shop;

use \skeeks\cms\shop\models\ShopDiscountCoupon as SXShopDiscountCoupon;
use skeeks\cms\models\CmsUser;
use skeeks\cms\helpers\StringHelper;

/**
 * @property ShopDiscount $shopDiscount
 */
class ShopDiscountCoupon extends SXShopDiscountCoupon
{
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'shop_discount_id', 'is_active', 'active_from', 'active_to', 'max_use', 'use_count', 'cms_user_id'], 'integer'],
//            [['shop_discount_id',
//                //'coupon'
//            ], 'required'],
            [['coupon'], 'string', 'max' => 32],
            [['description'], 'string', 'max' => 255],
            [['cms_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['cms_user_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['shop_discount_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopDiscount::className(), 'targetAttribute' => ['shop_discount_id' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['updated_by' => 'id']],

            [['coupon'], 'default', 'value' => function()
            {
                return "SO-" . StringHelper::strtoupper(\Yii::$app->security->generateRandomString(15));
            }],
        ];
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels['coupon'] = 'Промокод';
        $labels['max_use'] = 'Максимальное число использований (0 - бесконечное число использований)';
        $labels['use_count'] = 'Использован раз';
        return $labels;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopDiscount()
    {
        return $this->hasOne(ShopDiscount::className(), ['id' => 'shop_discount_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrder2discountCoupons()
    {
        return $this->hasMany(ShopOrder2discountCoupon::className(), ['discount_coupon_id' => 'id']);
    }

    /**
     * @param string $couponCode
     *
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getActiveCouponByCode($couponCode)
    {
        return static::find()
            ->where(['coupon' => $couponCode])
            ->andWhere(['is_active' => 1])
            ->andWhere(['OR',['<=', 'active_from', time()], ['active_from' => null]])
            ->andWhere(['OR',['>=', 'active_to', time()], ['active_to' => null]])
            ->one();
    }

    /**
     * Проверяет, соответствует ли купон данной акции хотя бы одному продукту корзины
     * @param ShopFuser $shopFuser
     *
     * @return bool
     */
    public function canApply(ShopFuser $shopFuser)
    {
        /** @var ShopBasket $shopBasket */
        foreach ($shopFuser->shopBaskets as $shopBasket) {
            if($this->shopDiscount->canApply($shopBasket, true)) return true;
        }

        return false;
    }
}