<?php

namespace modules\shopandshow\models\shop;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;


/**
 * This is the model class for table "ss_shop_fuser_discount".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $shop_fuser_id
 * @property string  $discount_name
 * @property string  $discount_price
 * @property integer $shop_order_id
 * @property integer $free_delivery_discount_id
 *
 * @property ShopFuser $shopFuser
 * @property ShopOrder $shopOrder
 * @property ShopDiscount $freeDeliveryDiscount
 */
class SsShopFuserDiscount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ss_shop_fuser_discount}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'created_by', 'updated_by', 'shop_fuser_id', 'shop_order_id', 'free_delivery_discount_id'], 'integer'],
            //[['shop_fuser_id'], 'required'],
            [['discount_price'], 'number'],
            [['discount_price'], 'default', 'value' => 0],
            [['discount_name'], 'string', 'max' => 255],
            [['free_delivery_discount_id'], 'exist', 'skipOnError' => true,
                'targetClass' => ShopDiscount::className(), 'targetAttribute' => ['free_delivery_discount_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'shop_fuser_id' => 'Корзина',
            'discount_name' => 'Акция',
            'discount_price' => 'Сумма скидки',
            'shop_order_id' => 'Заказ',
            'free_delivery_discount_id' => 'Бесплатная доставка'
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            BlameableBehavior::className(),
            TimestampBehavior::className(),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopFuser()
    {
        return $this->hasOne(ShopFuser::className(), ['id' => 'shop_fuser_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrder()
    {
        return $this->hasOne(ShopOrder::className(), ['id' => 'shop_order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFreeDeliveryDiscount()
    {
        return $this->hasOne(ShopDiscount::className(), ['id' => 'free_delivery_discount_id']);
    }
}
