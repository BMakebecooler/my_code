<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_discount".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $site_id Site ID
 * @property string $active Active
 * @property integer $active_from Active From
 * @property integer $active_to Active To
 * @property string $renewal Renewal
 * @property string $name Name
 * @property integer $max_uses Max Uses
 * @property integer $count_uses Count Uses
 * @property string $coupon Coupon
 * @property string $max_discount Max Discount
 * @property string $value_type Value Type
 * @property string $value Value
 * @property string $currency_code Currency Code
 * @property string $min_order_sum Min Order Sum
 * @property string $notes Notes
 * @property integer $type Type
 * @property string $xml_id Xml ID
 * @property string $count_period Count Period
 * @property integer $count_size Count Size
 * @property string $count_type Count Type
 * @property integer $count_from Count From
 * @property integer $count_to Count To
 * @property integer $action_size Action Size
 * @property string $action_type Action Type
 * @property integer $priority Priority
 * @property string $last_discount Last Discount
 * @property string $conditions Conditions
 * @property string $unpack Unpack
 * @property integer $version Version
 * @property string $code Code
 * @property integer $bitrix_id Bitrix ID
 * @property integer $bo_id Bo ID
 * @property integer $image_id Image ID
 *
     * @property MoneyCurrency $currencyCode
     * @property CmsStorageFile $image
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
     * @property ShopDiscount2typePrice[] $shopDiscount2typePrices
     * @property ShopTypePrice[] $typePrices
     * @property ShopDiscountCoupon[] $shopDiscountCoupons
     * @property SsShopDiscountConfiguration[] $ssShopDiscountConfigurations
     * @property SsShopDiscountLogic[] $ssShopDiscountLogics
     * @property SsShopFuserDiscount[] $ssShopFuserDiscounts
    */
class ShopDiscount extends \common\ActiveRecord
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
        return 'shop_discount';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'site_id', 'active_from', 'active_to', 'max_uses', 'count_uses', 'type', 'count_size', 'count_from', 'count_to', 'action_size', 'priority', 'version', 'bitrix_id', 'bo_id', 'image_id'], 'integer'],
            [['max_discount', 'value', 'min_order_sum'], 'number'],
            [['currency_code'], 'required'],
            [['conditions', 'unpack'], 'string'],
            [['active', 'renewal', 'value_type', 'count_period', 'count_type', 'action_type', 'last_discount'], 'string', 'max' => 1],
            [['name', 'notes', 'xml_id', 'code'], 'string', 'max' => 255],
            [['coupon'], 'string', 'max' => 20],
            [['currency_code'], 'string', 'max' => 3],
            [['currency_code'], 'exist', 'skipOnError' => true, 'targetClass' => MoneyCurrency::className(), 'targetAttribute' => ['currency_code' => 'code']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsStorageFile::className(), 'targetAttribute' => ['image_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
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
            'site_id' => 'Site ID',
            'active' => 'Active',
            'active_from' => 'Active From',
            'active_to' => 'Active To',
            'renewal' => 'Renewal',
            'name' => 'Name',
            'max_uses' => 'Max Uses',
            'count_uses' => 'Count Uses',
            'coupon' => 'Coupon',
            'max_discount' => 'Max Discount',
            'value_type' => 'Value Type',
            'value' => 'Value',
            'currency_code' => 'Currency Code',
            'min_order_sum' => 'Min Order Sum',
            'notes' => 'Notes',
            'type' => 'Type',
            'xml_id' => 'Xml ID',
            'count_period' => 'Count Period',
            'count_size' => 'Count Size',
            'count_type' => 'Count Type',
            'count_from' => 'Count From',
            'count_to' => 'Count To',
            'action_size' => 'Action Size',
            'action_type' => 'Action Type',
            'priority' => 'Priority',
            'last_discount' => 'Last Discount',
            'conditions' => 'Conditions',
            'unpack' => 'Unpack',
            'version' => 'Version',
            'code' => 'Code',
            'bitrix_id' => 'Bitrix ID',
            'bo_id' => 'Bo ID',
            'image_id' => 'Image ID',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCurrencyCode()
    {
        return $this->hasOne($this->called_class_namespace . '\MoneyCurrency', ['code' => 'currency_code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getImage()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsStorageFile', ['id' => 'image_id']);
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
    public function getShopDiscount2typePrices()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDiscount2typePrice', ['discount_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getTypePrices()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopTypePrice', ['id' => 'type_price_id'])->viaTable('shop_discount2type_price', ['discount_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDiscountCoupons()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDiscountCoupon', ['shop_discount_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSsShopDiscountConfigurations()
    {
        return $this->hasMany($this->called_class_namespace . '\SsShopDiscountConfiguration', ['shop_discount_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSsShopDiscountLogics()
    {
        return $this->hasMany($this->called_class_namespace . '\SsShopDiscountLogic', ['shop_discount_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSsShopFuserDiscounts()
    {
        return $this->hasMany($this->called_class_namespace . '\SsShopFuserDiscount', ['free_delivery_discount_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ShopDiscountQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopDiscountQuery(get_called_class());
    }
}
