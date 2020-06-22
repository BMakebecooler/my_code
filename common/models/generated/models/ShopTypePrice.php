<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_type_price".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $code Code
 * @property string $xml_id Xml ID
 * @property string $name Name
 * @property string $description Description
 * @property integer $priority Priority
 * @property string $def Def
 * @property integer $guid_id Guid ID
 *
     * @property ShopDiscount2typePrice[] $shopDiscount2typePrices
     * @property ShopDiscount[] $discounts
     * @property ShopProduct[] $shopProducts
     * @property ShopProductPrice[] $shopProductPrices
     * @property ShopProduct[] $products
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
    */
class ShopTypePrice extends \common\ActiveRecord
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
        return 'shop_type_price';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'priority', 'guid_id'], 'integer'],
            [['name'], 'required'],
            [['description'], 'string'],
            [['code', 'xml_id', 'name'], 'string', 'max' => 255],
            [['def'], 'string', 'max' => 1],
            [['code'], 'unique'],
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
            'code' => 'Code',
            'xml_id' => 'Xml ID',
            'name' => 'Name',
            'description' => 'Description',
            'priority' => 'Priority',
            'def' => 'Def',
            'guid_id' => 'Guid ID',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDiscount2typePrices()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDiscount2typePrice', ['type_price_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getDiscounts()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDiscount', ['id' => 'discount_id'])->viaTable('shop_discount2type_price', ['type_price_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopProducts()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopProduct', ['trial_price_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopProductPrices()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopProductPrice', ['type_price_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getProducts()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopProduct', ['id' => 'product_id'])->viaTable('shop_product_price', ['type_price_id' => 'id']);
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
     * @inheritdoc
     * @return \common\models\query\ShopTypePriceQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopTypePriceQuery(get_called_class());
    }
}
