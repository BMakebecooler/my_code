<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_pay_system".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $name Name
 * @property integer $priority Priority
 * @property string $active Active
 * @property string $description Description
 * @property string $component Component
 * @property string $component_settings Component Settings
 *
     * @property ShopDelivery2paySystem[] $shopDelivery2paySystems
     * @property ShopDelivery[] $deliveries
     * @property ShopFuser[] $shopFusers
     * @property ShopOrder[] $shopOrders
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
     * @property ShopPaySystemPersonType[] $shopPaySystemPersonTypes
     * @property ShopPersonType[] $personTypes
    */
class ShopPaySystem extends \common\ActiveRecord
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
        return 'shop_pay_system';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'priority'], 'integer'],
            [['name'], 'required'],
            [['description', 'component_settings'], 'string'],
            [['name', 'component'], 'string', 'max' => 255],
            [['active'], 'string', 'max' => 1],
            [['name'], 'unique'],
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
            'priority' => 'Priority',
            'active' => 'Active',
            'description' => 'Description',
            'component' => 'Component',
            'component_settings' => 'Component Settings',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDelivery2paySystems()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDelivery2paySystem', ['pay_system_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getDeliveries()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDelivery', ['id' => 'delivery_id'])->viaTable('shop_delivery2pay_system', ['pay_system_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopFusers()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopFuser', ['pay_system_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopOrders()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopOrder', ['pay_system_id' => 'id']);
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
    public function getShopPaySystemPersonTypes()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopPaySystemPersonType', ['pay_system_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getPersonTypes()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopPersonType', ['id' => 'person_type_id'])->viaTable('shop_pay_system_person_type', ['pay_system_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ShopPaySystemQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopPaySystemQuery(get_called_class());
    }
}
