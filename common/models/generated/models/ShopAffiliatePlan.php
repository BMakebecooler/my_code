<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_affiliate_plan".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $site_code Site Code
 * @property string $name Name
 * @property string $description Description
 * @property string $active Active
 * @property string $base_rate Base Rate
 * @property string $base_rate_type Base Rate Type
 * @property string $base_rate_currency_code Base Rate Currency Code
 * @property string $min_pay Min Pay
 * @property string $min_plan_value Min Plan Value
 * @property string $value_currency_code Value Currency Code
 *
     * @property ShopAffiliate[] $shopAffiliates
     * @property CmsSite $siteCode
     * @property MoneyCurrency $baseRateCurrencyCode
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
     * @property MoneyCurrency $valueCurrencyCode
    */
class ShopAffiliatePlan extends \common\ActiveRecord
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
        return 'shop_affiliate_plan';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],
            [['site_code', 'name'], 'required'],
            [['description'], 'string'],
            [['base_rate', 'min_pay', 'min_plan_value'], 'number'],
            [['site_code'], 'string', 'max' => 15],
            [['name'], 'string', 'max' => 255],
            [['active', 'base_rate_type'], 'string', 'max' => 1],
            [['base_rate_currency_code', 'value_currency_code'], 'string', 'max' => 3],
            [['site_code'], 'exist', 'skipOnError' => true, 'targetClass' => CmsSite::className(), 'targetAttribute' => ['site_code' => 'code']],
            [['base_rate_currency_code'], 'exist', 'skipOnError' => true, 'targetClass' => MoneyCurrency::className(), 'targetAttribute' => ['base_rate_currency_code' => 'code']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['updated_by' => 'id']],
            [['value_currency_code'], 'exist', 'skipOnError' => true, 'targetClass' => MoneyCurrency::className(), 'targetAttribute' => ['value_currency_code' => 'code']],
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
            'site_code' => 'Site Code',
            'name' => 'Name',
            'description' => 'Description',
            'active' => 'Active',
            'base_rate' => 'Base Rate',
            'base_rate_type' => 'Base Rate Type',
            'base_rate_currency_code' => 'Base Rate Currency Code',
            'min_pay' => 'Min Pay',
            'min_plan_value' => 'Min Plan Value',
            'value_currency_code' => 'Value Currency Code',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopAffiliates()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopAffiliate', ['plan_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSiteCode()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsSite', ['code' => 'site_code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getBaseRateCurrencyCode()
    {
        return $this->hasOne($this->called_class_namespace . '\MoneyCurrency', ['code' => 'base_rate_currency_code']);
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
    public function getValueCurrencyCode()
    {
        return $this->hasOne($this->called_class_namespace . '\MoneyCurrency', ['code' => 'value_currency_code']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ShopAffiliatePlanQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopAffiliatePlanQuery(get_called_class());
    }
}
