<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_discount_coupon".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $shop_discount_id Shop Discount ID
 * @property integer $is_active Is Active
 * @property integer $active_from Active From
 * @property integer $active_to Active To
 * @property string $coupon Coupon
 * @property integer $max_use Max Use
 * @property integer $use_count Use Count
 * @property integer $cms_user_id Cms User ID
 * @property string $description Description
 *
     * @property CmsUser $cmsUser
     * @property CmsUser $createdBy
     * @property ShopDiscount $shopDiscount
     * @property CmsUser $updatedBy
     * @property ShopOrder2discountCoupon[] $shopOrder2discountCoupons
    */
class ShopDiscountCoupon extends \common\ActiveRecord
{
    private $called_class_namespace;

    public function __construct()
    {
        $this->called_class_namespace = substr(get_called_class(), 0, strrpos(get_called_class(), '\\'));
        parent::__construct();
    }

                                                        
    /**
     * @inheritdoc
    */
    public function behaviors()
    {
        return [
            'author' => \yii\behaviors\BlameableBehavior::class,
            'timestamp' => \yii\behaviors\TimestampBehavior::class,
        ];
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'shop_discount_coupon';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'shop_discount_id', 'is_active', 'active_from', 'active_to', 'max_use', 'use_count', 'cms_user_id'], 'integer'],
            [['coupon'], 'required'],
            [['coupon'], 'string', 'max' => 32],
            [['description'], 'string', 'max' => 255],
            [['cms_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['cms_user_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['shop_discount_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopDiscount::className(), 'targetAttribute' => ['shop_discount_id' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'shop_discount_id' => 'Shop Discount ID',
            'is_active' => 'Is Active',
            'active_from' => 'Active From',
            'active_to' => 'Active To',
            'coupon' => 'Coupon',
            'max_use' => 'Max Use',
            'use_count' => 'Use Count',
            'cms_user_id' => 'Cms User ID',
            'description' => 'Description',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsUser()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'cms_user_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCreatedBy()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'created_by']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDiscount()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopDiscount', ['id' => 'shop_discount_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getUpdatedBy()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'updated_by']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopOrder2discountCoupons()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopOrder2discountCoupon', ['discount_coupon_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ShopDiscountCouponQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopDiscountCouponQuery(get_called_class());
    }
}
