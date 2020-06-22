<?php


namespace common\models;


use common\models\query\ShopProductPriceQuery;

class ShopProductPrice extends \common\models\generated\models\ShopProductPrice
{
    public static function find()
    {
        return new ShopProductPriceQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'product_id', 'type_price_id', 'quantity_from', 'quantity_to'], 'integer'],
            [['product_id', 'type_price_id'], 'required'],
            [['price'], 'number'],
            [['currency_code'], 'string', 'max' => 3],
            [['tmp_id'], 'string', 'max' => 40],
            [['currency_code'], 'default', 'value' => 'RUB'],
            [['price'], 'default', 'value' => 0.00],
        ];
    }
}