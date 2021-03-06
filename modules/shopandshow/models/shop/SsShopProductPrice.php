<?php

namespace modules\shopandshow\models\shop;

use skeeks\cms\shop\models\ShopTypePrice;
use Yii;
use yii\base\Event;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ss_shop_product_prices".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $product_id
 * @property integer $type_price_id
 * @property string $price
 * @property string $min_price
 * @property string $max_price
 * @property string $discount_percent
 */
class SsShopProductPrice extends \yii\db\ActiveRecord
{
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
            [['product_id', 'type_price_id'], 'integer'],
            [['price', 'min_price', 'max_price', 'discount_percent'], 'number'],
            [['product_id'], 'unique'],
        ];
    }

    public function behaviors()
    {
        $parent = parent::behaviors(); // TODO: Change the autogenerated stub

        return ArrayHelper::merge($parent,
            [
                'time' => [
                    'class' => TimestampBehavior::className(),
                    'value' => new Expression('NOW()'),
                ]
            ]);
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
            'discount_percent' => 'discount_percent',
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTypePrice()
    {
        return $this->hasOne(ShopTypePrice::className(), ['id' => 'type_price_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(ShopProduct::className(), ['id' => 'product_id']);
    }

    /**
     * ShopCmsContentElement $element
     * @param $element
     * @return int
     */
    public static function changePrice($element)
    {

        return false;
        $connection = Yii::$app->db;

        if ($element instanceof Event) {
            $element = $element->sender;
        }

        \Yii::$app->db->createCommand("SET @product_id = :product_id, @base_price_id = (SELECT id FROM shop_type_price WHERE def = 'Y');", [
            ':product_id' => $element->id
        ])->execute();

        $updatePriceSql = <<<SQL
SET sql_mode = '';
    
REPLACE INTO ss_shop_product_prices (updated_at, product_id, type_price_id, price, min_price, max_price, discount_percent)
    
    SELECT NOW(), product.id AS product_id, COALESCE(stp_offer.id, @base_price_id) AS type_price_id, COALESCE(sp_offer.price, sp_base.price) AS price,
        MIN(COALESCE(sp_min_max_offer.price, sp_min_max_product.price)) AS min_price, 
        MAX(COALESCE(sp_min_max_offer.price, sp_min_max_product.price)) AS max_price,
        ROUND(((max_price - min_price) / max_price) * 100 )
        
    FROM cms_content_element AS product
        
    LEFT JOIN cms_content_element parent_products ON parent_products.parent_content_element_id = product.id
    
    LEFT JOIN cms_content_element_property price_active_id ON price_active_id.element_id = product.id 
       AND price_active_id.property_id = (SELECT id FROM cms_content_property WHERE code = 'PRICE_ACTIVE' AND content_id = 2)
    
    LEFT JOIN cms_content_element_property price_active_type ON  price_active_type.element_id = price_active_id.value 
        AND price_active_type.property_id = (SELECT id FROM cms_content_property WHERE code = 'PRICE_CODE')
    
    LEFT JOIN shop_type_price AS stp_offer ON stp_offer.code = price_active_type.value

    LEFT JOIN shop_product_price AS sp_offer ON sp_offer.product_id = parent_products.id AND (sp_offer.type_price_id = stp_offer.id OR sp_offer.type_price_id = @base_price_id) 
    LEFT JOIN shop_product_price AS sp_base ON sp_base.type_price_id = @base_price_id AND sp_base.product_id = product.id
    
    LEFT JOIN shop_product_price AS sp_min_max_offer ON sp_min_max_offer.product_id IN (parent_products.id)
    LEFT JOIN shop_product_price AS sp_min_max_product ON sp_min_max_product.product_id = product.id
    
WHERE product.id = @product_id
SQL;

        return $connection->createCommand($updatePriceSql)->execute();
    }
}
