<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "minimum_prices".
 *
 * @property integer $product_id Product ID
 * @property integer $type_price_id Type Price ID
 * @property string $min_price Min Price
 * @property integer $count_prices Count Prices
*/
class MinimumPrices extends \common\ActiveRecord
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
    public static function tableName()
    {
        return 'minimum_prices';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['product_id', 'type_price_id', 'min_price'], 'required'],
            [['product_id', 'type_price_id', 'count_prices'], 'integer'],
            [['min_price'], 'number'],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'product_id' => 'Product ID',
            'type_price_id' => 'Type Price ID',
            'min_price' => 'Min Price',
            'count_prices' => 'Count Prices',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\MinimumPricesQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\MinimumPricesQuery(get_called_class());
    }
}
