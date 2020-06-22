<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_shop_fuser_discount".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $shop_fuser_id Shop Fuser ID
 * @property string $discount_name Discount Name
 * @property string $discount_price Discount Price
 * @property integer $shop_order_id Shop Order ID
 * @property integer $free_delivery_discount_id Free Delivery Discount ID
 *
     * @property ShopOrder $shopOrder
     * @property ShopDiscount $freeDeliveryDiscount
     * @property ShopFuser $shopFuser
    */
class SsShopFuserDiscount extends \common\ActiveRecord
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
        return 'ss_shop_fuser_discount';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'shop_fuser_id', 'shop_order_id', 'free_delivery_discount_id'], 'integer'],
            [['discount_price'], 'number'],
            [['discount_name'], 'string', 'max' => 255],
            [['shop_order_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopOrder::className(), 'targetAttribute' => ['shop_order_id' => 'id']],
            [['free_delivery_discount_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopDiscount::className(), 'targetAttribute' => ['free_delivery_discount_id' => 'id']],
            [['shop_fuser_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopFuser::className(), 'targetAttribute' => ['shop_fuser_id' => 'id']],
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
            'shop_fuser_id' => 'Shop Fuser ID',
            'discount_name' => 'Discount Name',
            'discount_price' => 'Discount Price',
            'shop_order_id' => 'Shop Order ID',
            'free_delivery_discount_id' => 'Free Delivery Discount ID',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopOrder()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopOrder', ['id' => 'shop_order_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getFreeDeliveryDiscount()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopDiscount', ['id' => 'free_delivery_discount_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopFuser()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopFuser', ['id' => 'shop_fuser_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsShopFuserDiscountQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsShopFuserDiscountQuery(get_called_class());
    }
}
