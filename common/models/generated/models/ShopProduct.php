<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_product".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property double $quantity Quantity
 * @property string $quantity_trace Quantity Trace
 * @property double $weight Weight
 * @property string $price_type Price Type
 * @property integer $recur_scheme_length Recur Scheme Length
 * @property string $recur_scheme_type Recur Scheme Type
 * @property integer $trial_price_id Trial Price ID
 * @property string $without_order Without Order
 * @property string $select_best_price Select Best Price
 * @property integer $vat_id Vat ID
 * @property string $vat_included Vat Included
 * @property string $tmp_id Tmp ID
 * @property string $can_buy_zero Can Buy Zero
 * @property string $negative_amount_trace Negative Amount Trace
 * @property string $barcode_multi Barcode Multi
 * @property string $purchasing_price Purchasing Price
 * @property string $purchasing_currency Purchasing Currency
 * @property double $quantity_reserved Quantity Reserved
 * @property integer $measure_id Measure ID
 * @property double $measure_ratio Measure Ratio
 * @property double $width Width
 * @property double $length Length
 * @property double $height Height
 * @property string $subscribe Subscribe
 * @property string $product_type Product Type
 *
     * @property ShopBasket[] $shopBaskets
     * @property ShopFuserFavorites[] $shopFuserFavorites
     * @property CmsContentElement $id0
     * @property CmsUser $createdBy
     * @property Measure $measure
     * @property MoneyCurrency $purchasingCurrency
     * @property ShopTypePrice $trialPrice
     * @property ShopVat $vat
     * @property CmsUser $updatedBy
     * @property ShopProductPrice[] $shopProductPrices
     * @property ShopTypePrice[] $typePrices
     * @property ShopProductQuantityChange[] $shopProductQuantityChanges
     * @property ShopQuantityNoticeEmail[] $shopQuantityNoticeEmails
     * @property ShopViewedProduct[] $shopViewedProducts
    */
class ShopProduct extends \common\ActiveRecord
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
        return 'shop_product';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'recur_scheme_length', 'trial_price_id', 'vat_id', 'measure_id'], 'integer'],
            [['quantity', 'weight', 'purchasing_price', 'quantity_reserved', 'measure_ratio', 'width', 'length', 'height'], 'number'],
            [['quantity_trace', 'price_type', 'recur_scheme_type', 'without_order', 'select_best_price', 'vat_included', 'can_buy_zero', 'negative_amount_trace', 'barcode_multi', 'subscribe'], 'string', 'max' => 1],
            [['tmp_id'], 'string', 'max' => 40],
            [['purchasing_currency'], 'string', 'max' => 3],
            [['product_type'], 'string', 'max' => 10],
            [['id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['measure_id'], 'exist', 'skipOnError' => true, 'targetClass' => Measure::className(), 'targetAttribute' => ['measure_id' => 'id']],
            [['purchasing_currency'], 'exist', 'skipOnError' => true, 'targetClass' => MoneyCurrency::className(), 'targetAttribute' => ['purchasing_currency' => 'code']],
            [['trial_price_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopTypePrice::className(), 'targetAttribute' => ['trial_price_id' => 'id']],
            [['vat_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopVat::className(), 'targetAttribute' => ['vat_id' => 'id']],
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
            'quantity' => 'Quantity',
            'quantity_trace' => 'Quantity Trace',
            'weight' => 'Weight',
            'price_type' => 'Price Type',
            'recur_scheme_length' => 'Recur Scheme Length',
            'recur_scheme_type' => 'Recur Scheme Type',
            'trial_price_id' => 'Trial Price ID',
            'without_order' => 'Without Order',
            'select_best_price' => 'Select Best Price',
            'vat_id' => 'Vat ID',
            'vat_included' => 'Vat Included',
            'tmp_id' => 'Tmp ID',
            'can_buy_zero' => 'Can Buy Zero',
            'negative_amount_trace' => 'Negative Amount Trace',
            'barcode_multi' => 'Barcode Multi',
            'purchasing_price' => 'Purchasing Price',
            'purchasing_currency' => 'Purchasing Currency',
            'quantity_reserved' => 'Quantity Reserved',
            'measure_id' => 'Measure ID',
            'measure_ratio' => 'Measure Ratio',
            'width' => 'Width',
            'length' => 'Length',
            'height' => 'Height',
            'subscribe' => 'Subscribe',
            'product_type' => 'Product Type',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopBaskets()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopBasket', ['product_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopFuserFavorites()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopFuserFavorites', ['shop_product_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getId0()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContentElement', ['id' => 'id']);
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
    public function getMeasure()
    {
        return $this->hasOne($this->called_class_namespace . '\Measure', ['id' => 'measure_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getPurchasingCurrency()
    {
        return $this->hasOne($this->called_class_namespace . '\MoneyCurrency', ['code' => 'purchasing_currency']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getTrialPrice()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopTypePrice', ['id' => 'trial_price_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getVat()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopVat', ['id' => 'vat_id']);
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
    public function getShopProductPrices()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopProductPrice', ['product_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getTypePrices()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopTypePrice', ['id' => 'type_price_id'])->viaTable('shop_product_price', ['product_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopProductQuantityChanges()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopProductQuantityChange', ['shop_product_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopQuantityNoticeEmails()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopQuantityNoticeEmail', ['shop_product_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopViewedProducts()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopViewedProduct', ['shop_product_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ShopProductQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopProductQuery(get_called_class());
    }
}
