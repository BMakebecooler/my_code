<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_product_price".
 *
 * @property string $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $product_id Product ID
 * @property integer $type_price_id Type Price ID
 * @property string $price Price
 * @property string $currency_code Currency Code
 * @property integer $quantity_from Quantity From
 * @property integer $quantity_to Quantity To
 * @property string $tmp_id Tmp ID
 *
     * @property MoneyCurrency $currencyCode
     * @property CmsUser $createdBy
     * @property ShopProduct $product
     * @property ShopTypePrice $typePrice
     * @property CmsUser $updatedBy
    */
class ShopProductPrice extends \common\ActiveRecord
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
        return 'shop_product_price';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'product_id', 'type_price_id', 'quantity_from', 'quantity_to'], 'integer'],
            [['product_id', 'type_price_id', 'price', 'currency_code'], 'required'],
            [['price'], 'number'],
            [['currency_code'], 'string', 'max' => 3],
            [['tmp_id'], 'string', 'max' => 40],
            [['product_id', 'type_price_id'], 'unique', 'targetAttribute' => ['product_id', 'type_price_id'], 'message' => 'The combination of Product ID and Type Price ID has already been taken.'],
            [['currency_code'], 'exist', 'skipOnError' => true, 'targetClass' => MoneyCurrency::className(), 'targetAttribute' => ['currency_code' => 'code']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopProduct::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['type_price_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopTypePrice::className(), 'targetAttribute' => ['type_price_id' => 'id']],
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
            'product_id' => 'Product ID',
            'type_price_id' => 'Type Price ID',
            'price' => 'Price',
            'currency_code' => 'Currency Code',
            'quantity_from' => 'Quantity From',
            'quantity_to' => 'Quantity To',
            'tmp_id' => 'Tmp ID',
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
    public function getCreatedBy()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'created_by']);
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
    public function getTypePrice()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopTypePrice', ['id' => 'type_price_id']);
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
     * @return \common\models\query\ShopProductPriceQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopProductPriceQuery(get_called_class());
    }
}
