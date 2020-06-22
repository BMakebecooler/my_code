<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_person_type".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $name Name
 * @property integer $priority Priority
 * @property string $active Active
 *
     * @property ShopBuyer[] $shopBuyers
     * @property ShopFuser[] $shopFusers
     * @property ShopOrder[] $shopOrders
     * @property ShopPaySystemPersonType[] $shopPaySystemPersonTypes
     * @property ShopPaySystem[] $paySystems
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
     * @property ShopPersonTypeProperty[] $shopPersonTypeProperties
     * @property ShopPersonTypeSite[] $shopPersonTypeSites
     * @property CmsSite[] $siteCodes
     * @property ShopTaxRate[] $shopTaxRates
    */
class ShopPersonType extends \common\ActiveRecord
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
        return 'shop_person_type';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'priority'], 'integer'],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
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
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopBuyers()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopBuyer', ['shop_person_type_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopFusers()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopFuser', ['person_type_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopOrders()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopOrder', ['person_type_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopPaySystemPersonTypes()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopPaySystemPersonType', ['person_type_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getPaySystems()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopPaySystem', ['id' => 'pay_system_id'])->viaTable('shop_pay_system_person_type', ['person_type_id' => 'id']);
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
    public function getShopPersonTypeProperties()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopPersonTypeProperty', ['shop_person_type_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopPersonTypeSites()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopPersonTypeSite', ['person_type_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSiteCodes()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsSite', ['code' => 'site_code'])->viaTable('shop_person_type_site', ['person_type_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopTaxRates()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopTaxRate', ['person_type_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ShopPersonTypeQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopPersonTypeQuery(get_called_class());
    }
}
