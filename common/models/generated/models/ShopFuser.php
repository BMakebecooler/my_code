<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_fuser".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $user_id User ID
 * @property string $additional Additional
 * @property integer $person_type_id Person Type ID
 * @property integer $site_id Site ID
 * @property integer $buyer_id Buyer ID
 * @property integer $pay_system_id Pay System ID
 * @property integer $delivery_id Delivery ID
 * @property integer $store_id Store ID
 * @property string $discount_coupons Discount Coupons
 * @property string $phone Phone
 * @property integer $external_order_id External Order ID
 * @property string $pvz_data Pvz Data
 *
     * @property ShopBasket[] $shopBaskets
     * @property ShopDelivery $delivery
     * @property ShopPaySystem $paySystem
     * @property ShopPersonType $personType
     * @property ShopBuyer $buyer
     * @property CmsSite $site
     * @property CmsContentElement $store
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
     * @property CmsUser $user
     * @property ShopFuserFavorites[] $shopFuserFavorites
     * @property ShopQuantityNoticeEmail[] $shopQuantityNoticeEmails
     * @property ShopViewedProduct[] $shopViewedProducts
     * @property SsShopFuserDiscount[] $ssShopFuserDiscounts
    */
class ShopFuser extends \common\ActiveRecord
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
        return 'shop_fuser';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'user_id', 'person_type_id', 'site_id', 'buyer_id', 'pay_system_id', 'delivery_id', 'store_id', 'external_order_id'], 'integer'],
            [['additional', 'discount_coupons', 'pvz_data'], 'string'],
            [['phone'], 'string', 'max' => 64],
            [['user_id'], 'unique'],
            [['delivery_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopDelivery::className(), 'targetAttribute' => ['delivery_id' => 'id']],
            [['pay_system_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopPaySystem::className(), 'targetAttribute' => ['pay_system_id' => 'id']],
            [['person_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopPersonType::className(), 'targetAttribute' => ['person_type_id' => 'id']],
            [['buyer_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopBuyer::className(), 'targetAttribute' => ['buyer_id' => 'id']],
            [['site_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsSite::className(), 'targetAttribute' => ['site_id' => 'id']],
            [['store_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['store_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['updated_by' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['user_id' => 'id']],
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
            'user_id' => 'User ID',
            'additional' => 'Additional',
            'person_type_id' => 'Person Type ID',
            'site_id' => 'Site ID',
            'buyer_id' => 'Buyer ID',
            'pay_system_id' => 'Pay System ID',
            'delivery_id' => 'Delivery ID',
            'store_id' => 'Store ID',
            'discount_coupons' => 'Discount Coupons',
            'phone' => 'Phone',
            'external_order_id' => 'External Order ID',
            'pvz_data' => 'Pvz Data',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopBaskets()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopBasket', ['fuser_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getDelivery()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopDelivery', ['id' => 'delivery_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getPaySystem()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopPaySystem', ['id' => 'pay_system_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getPersonType()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopPersonType', ['id' => 'person_type_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getBuyer()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopBuyer', ['id' => 'buyer_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSite()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsSite', ['id' => 'site_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getStore()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContentElement', ['id' => 'store_id']);
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
    public function getUpdatedBy()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'updated_by']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getUser()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'user_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopFuserFavorites()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopFuserFavorites', ['shop_fuser_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopQuantityNoticeEmails()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopQuantityNoticeEmail', ['shop_fuser_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopViewedProducts()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopViewedProduct', ['shop_fuser_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSsShopFuserDiscounts()
    {
        return $this->hasMany($this->called_class_namespace . '\SsShopFuserDiscount', ['shop_fuser_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ShopFuserQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopFuserQuery(get_called_class());
    }
}
