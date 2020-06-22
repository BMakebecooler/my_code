<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_shop_product_prices".
 *
 * @property string $id ID
 * @property string $created_at Created At
 * @property string $updated_at Updated At
 * @property integer $product_id Product ID
 * @property integer $type_price_id Type Price ID
 * @property string $price Price
 * @property string $min_price Min Price
 * @property string $max_price Max Price
 * @property integer $discount_percent Discount Percent
 *
     * @property CmsContentElement $product
    */
class SsShopProductPrices extends \common\ActiveRecord
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
            'timestamp' => \yii\behaviors\TimestampBehavior::class,
        ];
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'ss_shop_product_prices';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['product_id', 'type_price_id', 'price', 'min_price', 'max_price'], 'required'],
            [['product_id', 'type_price_id', 'discount_percent'], 'integer'],
            [['price', 'min_price', 'max_price'], 'number'],
            [['product_id'], 'unique'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['product_id' => 'id']],
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
            'product_id' => 'Product ID',
            'type_price_id' => 'Type Price ID',
            'price' => 'Price',
            'min_price' => 'Min Price',
            'max_price' => 'Max Price',
            'discount_percent' => 'Discount Percent',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getProduct()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContentElement', ['id' => 'product_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsShopProductPricesQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsShopProductPricesQuery(get_called_class());
    }
}
