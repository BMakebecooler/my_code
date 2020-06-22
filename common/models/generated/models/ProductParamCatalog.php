<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "product_param_catalog".
 *
 * @property integer $product_id Product ID
 * @property string $lot_num Lot Num
 * @property integer $category_id Category ID
 * @property string $name Name
 * @property double $price Price
 * @property double $price_old Price Old
 * @property integer $discount Discount
 * @property integer $rating Rating
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 *
     * @property CmsContentElement $product
    */
class ProductParamCatalog extends \common\ActiveRecord
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
        return 'product_param_catalog';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['lot_num', 'category_id', 'price', 'price_old', 'discount', 'rating'], 'required'],
            [['category_id', 'discount', 'rating', 'created_at', 'updated_at'], 'integer'],
            [['price', 'price_old'], 'number'],
            [['lot_num'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 250],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'product_id' => 'Product ID',
            'lot_num' => 'Lot Num',
            'category_id' => 'Category ID',
            'name' => 'Name',
            'price' => 'Price',
            'price_old' => 'Price Old',
            'discount' => 'Discount',
            'rating' => 'Rating',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
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
     * @return \common\models\query\ProductParamCatalogQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ProductParamCatalogQuery(get_called_class());
    }
}
