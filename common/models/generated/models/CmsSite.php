<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "cms_site".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $active Active
 * @property string $def Def
 * @property integer $priority Priority
 * @property string $code Code
 * @property string $name Name
 * @property string $server_name Server Name
 * @property string $description Description
 * @property integer $image_id Image ID
 *
     * @property CmsComponentSettings[] $cmsComponentSettings
     * @property CmsSearchPhrase[] $cmsSearchPhrases
     * @property CmsStorageFile $image
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
     * @property CmsSiteDomain[] $cmsSiteDomains
     * @property CmsTree[] $cmsTrees
     * @property Form2FormSend[] $form2FormSends
     * @property Reviews2Message[] $reviews2Messages
     * @property ShopAffiliate[] $shopAffiliates
     * @property CmsUser[] $users
     * @property ShopAffiliatePlan[] $shopAffiliatePlans
     * @property ShopAffiliateTier $shopAffiliateTier
     * @property ShopBasket[] $shopBaskets
     * @property ShopDelivery[] $shopDeliveries
     * @property ShopFuser[] $shopFusers
     * @property ShopOrder[] $shopOrders
     * @property ShopPersonTypeSite[] $shopPersonTypeSites
     * @property ShopPersonType[] $personTypes
     * @property ShopStore[] $shopStores
     * @property ShopTax[] $shopTaxes
     * @property ShopViewedProduct[] $shopViewedProducts
    */
class CmsSite extends \common\ActiveRecord
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
        return 'cms_site';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'priority', 'image_id'], 'integer'],
            [['code', 'name'], 'required'],
            [['active', 'def'], 'string', 'max' => 1],
            [['code'], 'string', 'max' => 15],
            [['name', 'server_name', 'description'], 'string', 'max' => 255],
            [['code'], 'unique'],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsStorageFile::className(), 'targetAttribute' => ['image_id' => 'id']],
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
            'active' => 'Active',
            'def' => 'Def',
            'priority' => 'Priority',
            'code' => 'Code',
            'name' => 'Name',
            'server_name' => 'Server Name',
            'description' => 'Description',
            'image_id' => 'Image ID',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsComponentSettings()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsComponentSettings', ['site_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsSearchPhrases()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsSearchPhrase', ['site_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getImage()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsStorageFile', ['id' => 'image_id']);
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
    public function getCmsSiteDomains()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsSiteDomain', ['site_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTrees()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTree', ['site_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getForm2FormSends()
    {
        return $this->hasMany($this->called_class_namespace . '\Form2FormSend', ['site_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getReviews2Messages()
    {
        return $this->hasMany($this->called_class_namespace . '\Reviews2Message', ['site_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopAffiliates()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopAffiliate', ['site_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getUsers()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsUser', ['id' => 'user_id'])->viaTable('shop_affiliate', ['site_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopAffiliatePlans()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopAffiliatePlan', ['site_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopAffiliateTier()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopAffiliateTier', ['site_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopBaskets()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopBasket', ['site_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDeliveries()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDelivery', ['site_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopFusers()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopFuser', ['site_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopOrders()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopOrder', ['site_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopPersonTypeSites()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopPersonTypeSite', ['site_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getPersonTypes()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopPersonType', ['id' => 'person_type_id'])->viaTable('shop_person_type_site', ['site_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopStores()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopStore', ['site_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopTaxes()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopTax', ['site_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopViewedProducts()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopViewedProduct', ['site_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\CmsSiteQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CmsSiteQuery(get_called_class());
    }
}
