<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_affiliate".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $site_code Site Code
 * @property integer $user_id User ID
 * @property integer $affiliate_id Affiliate ID
 * @property integer $plan_id Plan ID
 * @property string $active Active
 * @property string $paid_sum Paid Sum
 * @property string $approved_sum Approved Sum
 * @property string $pending_sum Pending Sum
 * @property integer $items_number Items Number
 * @property string $items_sum Items Sum
 * @property integer $last_calculate_at Last Calculate At
 * @property string $aff_site Aff Site
 * @property string $aff_description Aff Description
 * @property string $fix_plan Fix Plan
 *
     * @property ShopAffiliate $affiliate
     * @property ShopAffiliate[] $shopAffiliates
     * @property ShopAffiliatePlan $plan
     * @property CmsSite $siteCode
     * @property CmsUser $user
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
     * @property ShopOrder[] $shopOrders
    */
class ShopAffiliate extends \common\ActiveRecord
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
        return 'shop_affiliate';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'user_id', 'affiliate_id', 'plan_id', 'items_number', 'last_calculate_at'], 'integer'],
            [['site_code', 'user_id', 'plan_id'], 'required'],
            [['paid_sum', 'approved_sum', 'pending_sum', 'items_sum'], 'number'],
            [['aff_description'], 'string'],
            [['site_code'], 'string', 'max' => 15],
            [['active', 'fix_plan'], 'string', 'max' => 1],
            [['aff_site'], 'string', 'max' => 255],
            [['user_id', 'site_code'], 'unique', 'targetAttribute' => ['user_id', 'site_code'], 'message' => 'The combination of Site Code and User ID has already been taken.'],
            [['affiliate_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopAffiliate::className(), 'targetAttribute' => ['affiliate_id' => 'id']],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopAffiliatePlan::className(), 'targetAttribute' => ['plan_id' => 'id']],
            [['site_code'], 'exist', 'skipOnError' => true, 'targetClass' => CmsSite::className(), 'targetAttribute' => ['site_code' => 'code']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['user_id' => 'id']],
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
            'site_code' => 'Site Code',
            'user_id' => 'User ID',
            'affiliate_id' => 'Affiliate ID',
            'plan_id' => 'Plan ID',
            'active' => 'Active',
            'paid_sum' => 'Paid Sum',
            'approved_sum' => 'Approved Sum',
            'pending_sum' => 'Pending Sum',
            'items_number' => 'Items Number',
            'items_sum' => 'Items Sum',
            'last_calculate_at' => 'Last Calculate At',
            'aff_site' => 'Aff Site',
            'aff_description' => 'Aff Description',
            'fix_plan' => 'Fix Plan',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getAffiliate()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopAffiliate', ['id' => 'affiliate_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopAffiliates()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopAffiliate', ['affiliate_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getPlan()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopAffiliatePlan', ['id' => 'plan_id']);
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
    public function getUser()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'user_id']);
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
    public function getShopOrders()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopOrder', ['affiliate_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ShopAffiliateQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopAffiliateQuery(get_called_class());
    }
}
