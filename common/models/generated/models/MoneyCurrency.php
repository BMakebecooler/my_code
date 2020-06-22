<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "money_currency".
 *
 * @property integer $id ID
 * @property string $code Code
 * @property string $name Name
 * @property string $name_full Name Full
 * @property string $course Course
 * @property integer $priority Priority
 * @property string $active Active
 *
     * @property ShopAffiliatePlan[] $shopAffiliatePlans
     * @property ShopAffiliatePlan[] $shopAffiliatePlans0
     * @property ShopBasket[] $shopBaskets
     * @property ShopDelivery[] $shopDeliveries
     * @property ShopDelivery[] $shopDeliveries0
     * @property ShopDiscount[] $shopDiscounts
     * @property ShopOrder[] $shopOrders
     * @property ShopProduct[] $shopProducts
     * @property ShopProductPrice[] $shopProductPrices
     * @property ShopProductPriceChange[] $shopProductPriceChanges
     * @property ShopUserAccount[] $shopUserAccounts
     * @property CmsUser[] $users
     * @property ShopUserTransact[] $shopUserTransacts
    */
class MoneyCurrency extends \common\ActiveRecord
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
        return 'money_currency';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['code'], 'required'],
            [['course'], 'number'],
            [['priority'], 'integer'],
            [['code'], 'string', 'max' => 3],
            [['name', 'name_full'], 'string', 'max' => 255],
            [['active'], 'string', 'max' => 1],
            [['code'], 'unique'],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'name' => 'Name',
            'name_full' => 'Name Full',
            'course' => 'Course',
            'priority' => 'Priority',
            'active' => 'Active',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopAffiliatePlans()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopAffiliatePlan', ['base_rate_currency_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopAffiliatePlans0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopAffiliatePlan', ['value_currency_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopBaskets()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopBasket', ['currency_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDeliveries()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDelivery', ['currency_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDeliveries0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDelivery', ['order_currency_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDiscounts()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDiscount', ['currency_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopOrders()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopOrder', ['currency_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopProducts()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopProduct', ['purchasing_currency' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopProductPrices()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopProductPrice', ['currency_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopProductPriceChanges()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopProductPriceChange', ['currency_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopUserAccounts()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopUserAccount', ['currency_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getUsers()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsUser', ['id' => 'user_id'])->viaTable('shop_user_account', ['currency_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopUserTransacts()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopUserTransact', ['currency_code' => 'code']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\MoneyCurrencyQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\MoneyCurrencyQuery(get_called_class());
    }
}
