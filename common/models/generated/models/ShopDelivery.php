<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_delivery".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $name Name
 * @property integer $site_id Site ID
 * @property integer $period_from Period From
 * @property integer $period_to Period To
 * @property string $period_type Period Type
 * @property integer $weight_from Weight From
 * @property integer $weight_to Weight To
 * @property string $order_price_from Order Price From
 * @property string $order_price_to Order Price To
 * @property string $order_currency_code Order Currency Code
 * @property string $active Active
 * @property string $price Price
 * @property string $currency_code Currency Code
 * @property integer $priority Priority
 * @property string $description Description
 * @property integer $logo_id Logo ID
 * @property string $store Store
 *
     * @property DeliveryServices[] $deliveryServices
     * @property MoneyCurrency $currencyCode
     * @property CmsStorageFile $logo
     * @property MoneyCurrency $orderCurrencyCode
     * @property CmsSite $site
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
     * @property ShopDelivery2paySystem[] $shopDelivery2paySystems
     * @property ShopPaySystem[] $paySystems
     * @property ShopFuser[] $shopFusers
     * @property ShopOrder[] $shopOrders
    */
class ShopDelivery extends \common\ActiveRecord
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
        return 'shop_delivery';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'site_id', 'period_from', 'period_to', 'weight_from', 'weight_to', 'priority', 'logo_id'], 'integer'],
            [['name', 'price', 'currency_code'], 'required'],
            [['order_price_from', 'order_price_to', 'price'], 'number'],
            [['description', 'store'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['period_type', 'active'], 'string', 'max' => 1],
            [['order_currency_code', 'currency_code'], 'string', 'max' => 3],
            [['currency_code'], 'exist', 'skipOnError' => true, 'targetClass' => MoneyCurrency::className(), 'targetAttribute' => ['currency_code' => 'code']],
            [['logo_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsStorageFile::className(), 'targetAttribute' => ['logo_id' => 'id']],
            [['order_currency_code'], 'exist', 'skipOnError' => true, 'targetClass' => MoneyCurrency::className(), 'targetAttribute' => ['order_currency_code' => 'code']],
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
            'name' => 'Name',
            'site_id' => 'Site ID',
            'period_from' => 'Period From',
            'period_to' => 'Period To',
            'period_type' => 'Period Type',
            'weight_from' => 'Weight From',
            'weight_to' => 'Weight To',
            'order_price_from' => 'Order Price From',
            'order_price_to' => 'Order Price To',
            'order_currency_code' => 'Order Currency Code',
            'active' => 'Active',
            'price' => 'Price',
            'currency_code' => 'Currency Code',
            'priority' => 'Priority',
            'description' => 'Description',
            'logo_id' => 'Logo ID',
            'store' => 'Store',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getDeliveryServices()
    {
        return $this->hasMany($this->called_class_namespace . '\DeliveryServices', ['delivery_id' => 'id']);
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
    public function getLogo()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsStorageFile', ['id' => 'logo_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getOrderCurrencyCode()
    {
        return $this->hasOne($this->called_class_namespace . '\MoneyCurrency', ['code' => 'order_currency_code']);
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
    public function getShopDelivery2paySystems()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDelivery2paySystem', ['delivery_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getPaySystems()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopPaySystem', ['id' => 'pay_system_id'])->viaTable('shop_delivery2pay_system', ['delivery_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopFusers()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopFuser', ['delivery_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopOrders()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopOrder', ['delivery_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ShopDeliveryQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopDeliveryQuery(get_called_class());
    }
}
