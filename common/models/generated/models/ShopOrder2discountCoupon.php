<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_order2discount_coupon".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $discount_coupon_id Discount Coupon ID
 * @property integer $order_id Order ID
 *
     * @property CmsUser $createdBy
     * @property ShopDiscountCoupon $discountCoupon
     * @property ShopOrder $order
     * @property CmsUser $updatedBy
    */
class ShopOrder2discountCoupon extends \common\ActiveRecord
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
        return 'shop_order2discount_coupon';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'discount_coupon_id', 'order_id'], 'integer'],
            [['discount_coupon_id'], 'required'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['discount_coupon_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopDiscountCoupon::className(), 'targetAttribute' => ['discount_coupon_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopOrder::className(), 'targetAttribute' => ['order_id' => 'id']],
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
            'discount_coupon_id' => 'Discount Coupon ID',
            'order_id' => 'Order ID',
            ];
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
    public function getDiscountCoupon()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopDiscountCoupon', ['id' => 'discount_coupon_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getOrder()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopOrder', ['id' => 'order_id']);
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
     * @inheritdoc
     * @return \common\models\query\ShopOrder2discountCouponQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopOrder2discountCouponQuery(get_called_class());
    }
}