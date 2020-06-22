<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_basket".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $fuser_id Fuser ID
 * @property integer $order_id Order ID
 * @property integer $product_id Product ID
 * @property integer $product_price_id Product Price ID
 * @property string $price Price
 * @property string $currency_code Currency Code
 * @property string $weight Weight
 * @property string $quantity Quantity
 * @property integer $site_id Site ID
 * @property string $delay Delay
 * @property string $name Name
 * @property string $can_buy Can Buy
 * @property string $callback_func Callback Func
 * @property string $notes Notes
 * @property string $order_callback_func Order Callback Func
 * @property string $detail_page_url Detail Page Url
 * @property string $discount_price Discount Price
 * @property string $cancel_callback_func Cancel Callback Func
 * @property string $pay_callback_func Pay Callback Func
 * @property string $catalog_xml_id Catalog Xml ID
 * @property string $product_xml_id Product Xml ID
 * @property string $discount_name Discount Name
 * @property string $discount_value Discount Value
 * @property string $discount_coupon Discount Coupon
 * @property string $vat_rate Vat Rate
 * @property string $subscribe Subscribe
 * @property string $barcode_multi Barcode Multi
 * @property string $reserved Reserved
 * @property double $reserve_quantity Reserve Quantity
 * @property string $deducted Deducted
 * @property string $custom_price Custom Price
 * @property string $dimensions Dimensions
 * @property integer $type Type
 * @property integer $set_parent_id Set Parent ID
 * @property string $measure_name Measure Name
 * @property integer $measure_code Measure Code
 * @property string $recommendation Recommendation
 * @property integer $has_removed Has Removed
 * @property integer $main_product_id Main Product ID
 * @property integer $kfss_position_id Kfss Position ID
 *
     * @property CmsContentElement $mainProduct
     * @property MoneyCurrency $currencyCode
     * @property ShopFuser $fuser
     * @property ShopOrder $order
     * @property ShopProduct $product
     * @property CmsSite $site
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
     * @property ShopBasketProps[] $shopBasketProps
    */
class ShopBasket extends \common\ActiveRecord
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
        return 'shop_basket';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'fuser_id', 'order_id', 'product_id', 'product_price_id', 'site_id', 'type', 'set_parent_id', 'measure_code', 'has_removed', 'main_product_id', 'kfss_position_id'], 'integer'],
            [['price', 'currency_code', 'name'], 'required'],
            [['price', 'weight', 'quantity', 'discount_price', 'vat_rate', 'reserve_quantity'], 'number'],
            [['currency_code'], 'string', 'max' => 3],
            [['delay', 'can_buy', 'subscribe', 'barcode_multi', 'reserved', 'deducted', 'custom_price'], 'string', 'max' => 1],
            [['name', 'callback_func', 'notes', 'order_callback_func', 'detail_page_url', 'cancel_callback_func', 'pay_callback_func', 'discount_name', 'dimensions', 'recommendation'], 'string', 'max' => 255],
            [['catalog_xml_id', 'product_xml_id'], 'string', 'max' => 100],
            [['discount_value', 'discount_coupon'], 'string', 'max' => 32],
            [['measure_name'], 'string', 'max' => 50],
            [['main_product_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['main_product_id' => 'id']],
            [['currency_code'], 'exist', 'skipOnError' => true, 'targetClass' => MoneyCurrency::className(), 'targetAttribute' => ['currency_code' => 'code']],
            [['fuser_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopFuser::className(), 'targetAttribute' => ['fuser_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopOrder::className(), 'targetAttribute' => ['order_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopProduct::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['site_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsSite::className(), 'targetAttribute' => ['site_id' => 'id']],
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
            'fuser_id' => 'Fuser ID',
            'order_id' => 'Order ID',
            'product_id' => 'Product ID',
            'product_price_id' => 'Product Price ID',
            'price' => 'Price',
            'currency_code' => 'Currency Code',
            'weight' => 'Weight',
            'quantity' => 'Quantity',
            'site_id' => 'Site ID',
            'delay' => 'Delay',
            'name' => 'Name',
            'can_buy' => 'Can Buy',
            'callback_func' => 'Callback Func',
            'notes' => 'Notes',
            'order_callback_func' => 'Order Callback Func',
            'detail_page_url' => 'Detail Page Url',
            'discount_price' => 'Discount Price',
            'cancel_callback_func' => 'Cancel Callback Func',
            'pay_callback_func' => 'Pay Callback Func',
            'catalog_xml_id' => 'Catalog Xml ID',
            'product_xml_id' => 'Product Xml ID',
            'discount_name' => 'Discount Name',
            'discount_value' => 'Discount Value',
            'discount_coupon' => 'Discount Coupon',
            'vat_rate' => 'Vat Rate',
            'subscribe' => 'Subscribe',
            'barcode_multi' => 'Barcode Multi',
            'reserved' => 'Reserved',
            'reserve_quantity' => 'Reserve Quantity',
            'deducted' => 'Deducted',
            'custom_price' => 'Custom Price',
            'dimensions' => 'Dimensions',
            'type' => 'Type',
            'set_parent_id' => 'Set Parent ID',
            'measure_name' => 'Measure Name',
            'measure_code' => 'Measure Code',
            'recommendation' => 'Recommendation',
            'has_removed' => 'Has Removed',
            'main_product_id' => 'Main Product ID',
            'kfss_position_id' => 'Kfss Position ID',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getMainProduct()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContentElement', ['id' => 'main_product_id']);
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
    public function getFuser()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopFuser', ['id' => 'fuser_id']);
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
    public function getProduct()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopProduct', ['id' => 'product_id']);
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
    public function getShopBasketProps()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopBasketProps', ['shop_basket_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ShopBasketQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopBasketQuery(get_called_class());
    }
}
