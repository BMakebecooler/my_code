<?php

namespace common\models\generated\models;

use common\helpers\Strings;
use Yii;

/**
 * This is the model class for table "cms_user".
 *
 * @property integer $id ID
 * @property string $username Username
 * @property string $auth_key Auth Key
 * @property string $password_hash Password Hash
 * @property string $password_reset_token Password Reset Token
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $name Name
 * @property integer $image_id Image ID
 * @property string $gender Gender
 * @property string $active Active
 * @property integer $updated_by Updated By
 * @property integer $created_by Created By
 * @property integer $logged_at Logged At
 * @property integer $last_activity_at Last Activity At
 * @property integer $last_admin_activity_at Last Admin Activity At
 * @property string $email Email
 * @property string $phone Phone
 * @property integer $phone_int Phone Int
 * @property integer $email_is_approved Email Is Approved
 * @property integer $phone_is_approved Phone Is Approved
 * @property integer $bitrix_id Bitrix ID
 * @property integer $guid_id Guid ID
 * @property string $source Source
 * @property string $source_detail Source Detail
 * @property string $new_guid GUID
 * @property integer $kfss_status Status
 *
     * @property AuthAssignment[] $authAssignments
     * @property AuthItem[] $itemNames
     * @property CmsAdminFilter[] $cmsAdminFilters
     * @property CmsAdminFilter[] $cmsAdminFilters0
     * @property CmsAdminFilter[] $cmsAdminFilters1
     * @property CmsComponentSettings[] $cmsComponentSettings
     * @property CmsComponentSettings[] $cmsComponentSettings0
     * @property CmsComponentSettings[] $cmsComponentSettings1
     * @property CmsContent[] $cmsContents
     * @property CmsContent[] $cmsContents0
     * @property CmsContentElement[] $cmsContentElements
     * @property CmsContentElement[] $cmsContentElements0
     * @property CmsContentElement2cmsUser[] $cmsContentElement2cmsUsers
     * @property CmsContentElement2cmsUser[] $cmsContentElement2cmsUsers0
     * @property CmsContentElement2cmsUser[] $cmsContentElement2cmsUsers1
     * @property CmsContentElement[] $cmsContentElements1
     * @property CmsContentElementFaq[] $cmsContentElementFaqs
     * @property CmsContentElementFaq[] $cmsContentElementFaqs0
     * @property CmsContentElementFile[] $cmsContentElementFiles
     * @property CmsContentElementFile[] $cmsContentElementFiles0
     * @property CmsContentElementImage[] $cmsContentElementImages
     * @property CmsContentElementImage[] $cmsContentElementImages0
     * @property CmsContentElementProperty[] $cmsContentElementProperties
     * @property CmsContentElementProperty[] $cmsContentElementProperties0
     * @property CmsContentElementTree[] $cmsContentElementTrees
     * @property CmsContentElementTree[] $cmsContentElementTrees0
     * @property CmsContentProperty[] $cmsContentProperties
     * @property CmsContentProperty[] $cmsContentProperties0
     * @property CmsContentPropertyEnum[] $cmsContentPropertyEnums
     * @property CmsContentPropertyEnum[] $cmsContentPropertyEnums0
     * @property CmsContentType[] $cmsContentTypes
     * @property CmsContentType[] $cmsContentTypes0
     * @property CmsDashboard[] $cmsDashboards
     * @property CmsDashboard[] $cmsDashboards0
     * @property CmsDashboard[] $cmsDashboards1
     * @property CmsDashboardWidget[] $cmsDashboardWidgets
     * @property CmsDashboardWidget[] $cmsDashboardWidgets0
     * @property CmsLang[] $cmsLangs
     * @property CmsLang[] $cmsLangs0
     * @property CmsSearchPhrase[] $cmsSearchPhrases
     * @property CmsSearchPhrase[] $cmsSearchPhrases0
     * @property CmsSite[] $cmsSites
     * @property CmsSite[] $cmsSites0
     * @property CmsSiteDomain[] $cmsSiteDomains
     * @property CmsSiteDomain[] $cmsSiteDomains0
     * @property CmsStorageFile[] $cmsStorageFiles
     * @property CmsStorageFile[] $cmsStorageFiles0
     * @property CmsTree[] $cmsTrees
     * @property CmsTree[] $cmsTrees0
     * @property CmsTreeFile[] $cmsTreeFiles
     * @property CmsTreeFile[] $cmsTreeFiles0
     * @property CmsTreeImage[] $cmsTreeImages
     * @property CmsTreeImage[] $cmsTreeImages0
     * @property CmsTreeProperty[] $cmsTreeProperties
     * @property CmsTreeProperty[] $cmsTreeProperties0
     * @property CmsTreeType[] $cmsTreeTypes
     * @property CmsTreeType[] $cmsTreeTypes0
     * @property CmsTreeTypeProperty[] $cmsTreeTypeProperties
     * @property CmsTreeTypeProperty[] $cmsTreeTypeProperties0
     * @property CmsTreeTypePropertyEnum[] $cmsTreeTypePropertyEnums
     * @property CmsTreeTypePropertyEnum[] $cmsTreeTypePropertyEnums0
     * @property CmsStorageFile $image
     * @property CmsUser $createdBy
     * @property CmsUser[] $cmsUsers
     * @property CmsUser $updatedBy
     * @property CmsUser[] $cmsUsers0
     * @property CmsUserAuthclient[] $cmsUserAuthclients
     * @property CmsUserEmail[] $cmsUserEmails
     * @property CmsUserPhone[] $cmsUserPhones
     * @property CmsUserProperty[] $cmsUserProperties
     * @property CmsUserProperty[] $cmsUserProperties0
     * @property CmsUserProperty[] $cmsUserProperties1
     * @property CmsUserUniversalProperty[] $cmsUserUniversalProperties
     * @property CmsUserUniversalProperty[] $cmsUserUniversalProperties0
     * @property CmsUserUniversalPropertyEnum[] $cmsUserUniversalPropertyEnums
     * @property CmsUserUniversalPropertyEnum[] $cmsUserUniversalPropertyEnums0
     * @property ExportTask[] $exportTasks
     * @property ExportTask[] $exportTasks0
     * @property Form2Form[] $form2Forms
     * @property Form2Form[] $form2Forms0
     * @property Form2FormProperty[] $form2FormProperties
     * @property Form2FormProperty[] $form2FormProperties0
     * @property Form2FormPropertyEnum[] $form2FormPropertyEnums
     * @property Form2FormPropertyEnum[] $form2FormPropertyEnums0
     * @property Form2FormSend[] $form2FormSends
     * @property Form2FormSend[] $form2FormSends0
     * @property Form2FormSend[] $form2FormSends1
     * @property Form2FormSendProperty[] $form2FormSendProperties
     * @property Form2FormSendProperty[] $form2FormSendProperties0
     * @property KladrLocation[] $kladrLocations
     * @property KladrLocation[] $kladrLocations0
     * @property Measure[] $measures
     * @property Measure[] $measures0
     * @property Reviews2Message[] $reviews2Messages
     * @property Reviews2Message[] $reviews2Messages0
     * @property Reviews2Message[] $reviews2Messages1
     * @property SavedFilters[] $savedFilters
     * @property SavedFilters[] $savedFilters0
     * @property ShopAffiliate[] $shopAffiliates
     * @property ShopAffiliate[] $shopAffiliates0
     * @property ShopAffiliate[] $shopAffiliates1
     * @property CmsSite[] $siteCodes
     * @property ShopAffiliatePlan[] $shopAffiliatePlans
     * @property ShopAffiliatePlan[] $shopAffiliatePlans0
     * @property ShopAffiliateTier[] $shopAffiliateTiers
     * @property ShopAffiliateTier[] $shopAffiliateTiers0
     * @property ShopBasket[] $shopBaskets
     * @property ShopBasket[] $shopBaskets0
     * @property ShopBasketProps[] $shopBasketProps
     * @property ShopBasketProps[] $shopBasketProps0
     * @property ShopBuyer[] $shopBuyers
     * @property ShopBuyer[] $shopBuyers0
     * @property ShopBuyer[] $shopBuyers1
     * @property ShopBuyerProperty[] $shopBuyerProperties
     * @property ShopBuyerProperty[] $shopBuyerProperties0
     * @property ShopContent[] $shopContents
     * @property ShopContent[] $shopContents0
     * @property ShopDelivery[] $shopDeliveries
     * @property ShopDelivery[] $shopDeliveries0
     * @property ShopDelivery2paySystem[] $shopDelivery2paySystems
     * @property ShopDelivery2paySystem[] $shopDelivery2paySystems0
     * @property ShopDiscount[] $shopDiscounts
     * @property ShopDiscount[] $shopDiscounts0
     * @property ShopDiscount2typePrice[] $shopDiscount2typePrices
     * @property ShopDiscount2typePrice[] $shopDiscount2typePrices0
     * @property ShopDiscountCoupon[] $shopDiscountCoupons
     * @property ShopDiscountCoupon[] $shopDiscountCoupons0
     * @property ShopDiscountCoupon[] $shopDiscountCoupons1
     * @property ShopExtra[] $shopExtras
     * @property ShopExtra[] $shopExtras0
     * @property ShopFuser[] $shopFusers
     * @property ShopFuser[] $shopFusers0
     * @property ShopFuser $shopFuser
     * @property ShopOrder[] $shopOrders
     * @property ShopOrder[] $shopOrders0
     * @property ShopOrder[] $shopOrders1
     * @property ShopOrder[] $shopOrders2
     * @property ShopOrder2discountCoupon[] $shopOrder2discountCoupons
     * @property ShopOrder2discountCoupon[] $shopOrder2discountCoupons0
     * @property ShopOrderChange[] $shopOrderChanges
     * @property ShopOrderChange[] $shopOrderChanges0
     * @property ShopOrderStatus[] $shopOrderStatuses
     * @property ShopOrderStatus[] $shopOrderStatuses0
     * @property ShopPaySystem[] $shopPaySystems
     * @property ShopPaySystem[] $shopPaySystems0
     * @property ShopPersonType[] $shopPersonTypes
     * @property ShopPersonType[] $shopPersonTypes0
     * @property ShopPersonTypeProperty[] $shopPersonTypeProperties
     * @property ShopPersonTypeProperty[] $shopPersonTypeProperties0
     * @property ShopPersonTypePropertyEnum[] $shopPersonTypePropertyEnums
     * @property ShopPersonTypePropertyEnum[] $shopPersonTypePropertyEnums0
     * @property ShopProduct[] $shopProducts
     * @property ShopProduct[] $shopProducts0
     * @property ShopProductPrice[] $shopProductPrices
     * @property ShopProductPrice[] $shopProductPrices0
     * @property ShopProductPriceChange[] $shopProductPriceChanges
     * @property ShopProductPriceChange[] $shopProductPriceChanges0
     * @property ShopProductQuantityChange[] $shopProductQuantityChanges
     * @property ShopProductQuantityChange[] $shopProductQuantityChanges0
     * @property ShopQuantityNoticeEmail[] $shopQuantityNoticeEmails
     * @property ShopQuantityNoticeEmail[] $shopQuantityNoticeEmails0
     * @property ShopStore[] $shopStores
     * @property ShopStore[] $shopStores0
     * @property ShopTax[] $shopTaxes
     * @property ShopTax[] $shopTaxes0
     * @property ShopTaxRate[] $shopTaxRates
     * @property ShopTaxRate[] $shopTaxRates0
     * @property ShopTypePrice[] $shopTypePrices
     * @property ShopTypePrice[] $shopTypePrices0
     * @property ShopUserAccount[] $shopUserAccounts
     * @property ShopUserAccount[] $shopUserAccounts0
     * @property ShopUserAccount[] $shopUserAccounts1
     * @property MoneyCurrency[] $currencyCodes
     * @property ShopUserTransact[] $shopUserTransacts
     * @property ShopUserTransact[] $shopUserTransacts0
     * @property ShopUserTransact[] $shopUserTransacts1
     * @property ShopVat[] $shopVats
     * @property ShopVat[] $shopVats0
     * @property ShopViewedProduct[] $shopViewedProducts
     * @property ShopViewedProduct[] $shopViewedProducts0
     * @property SsUserVote[] $ssUserVotes
    */
class CmsUser extends \common\ActiveRecord
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
            'timestamp' => \yii\behaviors\TimestampBehavior::class,
            'author' => \yii\behaviors\BlameableBehavior::class,
        ];
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'cms_user';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['username', 'auth_key', 'password_hash'], 'required'],
            [['created_at', 'updated_at', 'image_id', 'updated_by', 'created_by', 'logged_at', 'last_activity_at', 'last_admin_activity_at', 'email_is_approved', 'phone_is_approved', 'bitrix_id', 'guid_id', 'kfss_status'], 'integer'],
            [['gender'], 'string'],
            [['username', 'password_hash', 'password_reset_token', 'name', 'email', 'source', 'source_detail'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['active'], 'string', 'max' => 1],
            [['phone', 'phone_int'], 'string', 'max' => 64],
            [['username'], 'unique'],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsStorageFile::className(), 'targetAttribute' => ['image_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['updated_by' => 'id']],
            [['phone'], 'filter', 'filter' => function($phone){
                return Strings::getPhoneClean($phone);
            }],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'name' => 'Name',
            'image_id' => 'Image ID',
            'gender' => 'Gender',
            'active' => 'Active',
            'updated_by' => 'Updated By',
            'created_by' => 'Created By',
            'logged_at' => 'Logged At',
            'last_activity_at' => 'Last Activity At',
            'last_admin_activity_at' => 'Last Admin Activity At',
            'email' => 'Email',
            'phone' => 'Phone',
            'phone_int' => 'Phone Int',
            'email_is_approved' => 'Email Is Approved',
            'phone_is_approved' => 'Phone Is Approved',
            'bitrix_id' => 'Bitrix ID',
            'guid_id' => 'Guid ID',
            'source' => 'Source',
            'source_detail' => 'Source Detail',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getAuthAssignments()
    {
        return $this->hasMany($this->called_class_namespace . '\AuthAssignment', ['user_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getItemNames()
    {
        return $this->hasMany($this->called_class_namespace . '\AuthItem', ['name' => 'item_name'])->viaTable('auth_assignment', ['user_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsAdminFilters()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsAdminFilter', ['cms_user_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsAdminFilters0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsAdminFilter', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsAdminFilters1()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsAdminFilter', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsComponentSettings()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsComponentSettings', ['user_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsComponentSettings0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsComponentSettings', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsComponentSettings1()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsComponentSettings', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContents()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContent', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContents0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContent', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElements()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElement', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElements0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElement', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElement2cmsUsers()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElement2cmsUser', ['cms_user_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElement2cmsUsers0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElement2cmsUser', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElement2cmsUsers1()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElement2cmsUser', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElements1()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElement', ['id' => 'cms_content_element_id'])->viaTable('cms_content_element2cms_user', ['cms_user_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElementFaqs()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElementFaq', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElementFaqs0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElementFaq', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElementFiles()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElementFile', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElementFiles0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElementFile', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElementImages()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElementImage', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElementImages0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElementImage', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElementProperties()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElementProperty', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElementProperties0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElementProperty', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElementTrees()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElementTree', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElementTrees0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElementTree', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentProperties()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentProperty', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentProperties0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentProperty', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentPropertyEnums()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentPropertyEnum', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentPropertyEnums0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentPropertyEnum', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentTypes()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentType', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentTypes0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentType', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsDashboards()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsDashboard', ['cms_user_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsDashboards0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsDashboard', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsDashboards1()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsDashboard', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsDashboardWidgets()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsDashboardWidget', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsDashboardWidgets0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsDashboardWidget', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsLangs()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsLang', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsLangs0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsLang', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsSearchPhrases()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsSearchPhrase', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsSearchPhrases0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsSearchPhrase', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsSites()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsSite', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsSites0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsSite', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsSiteDomains()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsSiteDomain', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsSiteDomains0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsSiteDomain', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsStorageFiles()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsStorageFile', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsStorageFiles0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsStorageFile', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTrees()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTree', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTrees0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTree', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTreeFiles()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTreeFile', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTreeFiles0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTreeFile', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTreeImages()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTreeImage', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTreeImages0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTreeImage', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTreeProperties()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTreeProperty', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTreeProperties0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTreeProperty', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTreeTypes()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTreeType', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTreeTypes0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTreeType', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTreeTypeProperties()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTreeTypeProperty', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTreeTypeProperties0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTreeTypeProperty', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTreeTypePropertyEnums()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTreeTypePropertyEnum', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTreeTypePropertyEnums0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTreeTypePropertyEnum', ['updated_by' => 'id']);
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
    public function getCmsUsers()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsUser', ['created_by' => 'id']);
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
    public function getCmsUsers0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsUser', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsUserAuthclients()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsUserAuthclient', ['user_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsUserEmails()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsUserEmail', ['user_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsUserPhones()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsUserPhone', ['user_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsUserProperties()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsUserProperty', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsUserProperties0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsUserProperty', ['element_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsUserProperties1()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsUserProperty', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsUserUniversalProperties()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsUserUniversalProperty', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsUserUniversalProperties0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsUserUniversalProperty', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsUserUniversalPropertyEnums()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsUserUniversalPropertyEnum', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsUserUniversalPropertyEnums0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsUserUniversalPropertyEnum', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getExportTasks()
    {
        return $this->hasMany($this->called_class_namespace . '\ExportTask', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getExportTasks0()
    {
        return $this->hasMany($this->called_class_namespace . '\ExportTask', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getForm2Forms()
    {
        return $this->hasMany($this->called_class_namespace . '\Form2Form', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getForm2Forms0()
    {
        return $this->hasMany($this->called_class_namespace . '\Form2Form', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getForm2FormProperties()
    {
        return $this->hasMany($this->called_class_namespace . '\Form2FormProperty', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getForm2FormProperties0()
    {
        return $this->hasMany($this->called_class_namespace . '\Form2FormProperty', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getForm2FormPropertyEnums()
    {
        return $this->hasMany($this->called_class_namespace . '\Form2FormPropertyEnum', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getForm2FormPropertyEnums0()
    {
        return $this->hasMany($this->called_class_namespace . '\Form2FormPropertyEnum', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getForm2FormSends()
    {
        return $this->hasMany($this->called_class_namespace . '\Form2FormSend', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getForm2FormSends0()
    {
        return $this->hasMany($this->called_class_namespace . '\Form2FormSend', ['processed_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getForm2FormSends1()
    {
        return $this->hasMany($this->called_class_namespace . '\Form2FormSend', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getForm2FormSendProperties()
    {
        return $this->hasMany($this->called_class_namespace . '\Form2FormSendProperty', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getForm2FormSendProperties0()
    {
        return $this->hasMany($this->called_class_namespace . '\Form2FormSendProperty', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getKladrLocations()
    {
        return $this->hasMany($this->called_class_namespace . '\KladrLocation', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getKladrLocations0()
    {
        return $this->hasMany($this->called_class_namespace . '\KladrLocation', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getMeasures()
    {
        return $this->hasMany($this->called_class_namespace . '\Measure', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getMeasures0()
    {
        return $this->hasMany($this->called_class_namespace . '\Measure', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getReviews2Messages()
    {
        return $this->hasMany($this->called_class_namespace . '\Reviews2Message', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getReviews2Messages0()
    {
        return $this->hasMany($this->called_class_namespace . '\Reviews2Message', ['processed_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getReviews2Messages1()
    {
        return $this->hasMany($this->called_class_namespace . '\Reviews2Message', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSavedFilters()
    {
        return $this->hasMany($this->called_class_namespace . '\SavedFilters', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSavedFilters0()
    {
        return $this->hasMany($this->called_class_namespace . '\SavedFilters', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopAffiliates()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopAffiliate', ['user_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopAffiliates0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopAffiliate', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopAffiliates1()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopAffiliate', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSiteCodes()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsSite', ['code' => 'site_code'])->viaTable('shop_affiliate', ['user_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopAffiliatePlans()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopAffiliatePlan', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopAffiliatePlans0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopAffiliatePlan', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopAffiliateTiers()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopAffiliateTier', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopAffiliateTiers0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopAffiliateTier', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopBaskets()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopBasket', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopBaskets0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopBasket', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopBasketProps()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopBasketProps', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopBasketProps0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopBasketProps', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopBuyers()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopBuyer', ['cms_user_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopBuyers0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopBuyer', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopBuyers1()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopBuyer', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopBuyerProperties()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopBuyerProperty', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopBuyerProperties0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopBuyerProperty', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopContents()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopContent', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopContents0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopContent', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDeliveries()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDelivery', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDeliveries0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDelivery', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDelivery2paySystems()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDelivery2paySystem', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDelivery2paySystems0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDelivery2paySystem', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDiscounts()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDiscount', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDiscounts0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDiscount', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDiscount2typePrices()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDiscount2typePrice', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDiscount2typePrices0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDiscount2typePrice', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDiscountCoupons()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDiscountCoupon', ['cms_user_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDiscountCoupons0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDiscountCoupon', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDiscountCoupons1()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDiscountCoupon', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopExtras()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopExtra', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopExtras0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopExtra', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopFusers()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopFuser', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopFusers0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopFuser', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopFuser()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopFuser', ['user_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopOrders()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopOrder', ['locked_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopOrders0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopOrder', ['user_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopOrders1()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopOrder', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopOrders2()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopOrder', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopOrder2discountCoupons()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopOrder2discountCoupon', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopOrder2discountCoupons0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopOrder2discountCoupon', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopOrderChanges()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopOrderChange', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopOrderChanges0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopOrderChange', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopOrderStatuses()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopOrderStatus', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopOrderStatuses0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopOrderStatus', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopPaySystems()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopPaySystem', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopPaySystems0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopPaySystem', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopPersonTypes()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopPersonType', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopPersonTypes0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopPersonType', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopPersonTypeProperties()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopPersonTypeProperty', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopPersonTypeProperties0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopPersonTypeProperty', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopPersonTypePropertyEnums()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopPersonTypePropertyEnum', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopPersonTypePropertyEnums0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopPersonTypePropertyEnum', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopProducts()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopProduct', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopProducts0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopProduct', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopProductPrices()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopProductPrice', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopProductPrices0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopProductPrice', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopProductPriceChanges()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopProductPriceChange', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopProductPriceChanges0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopProductPriceChange', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopProductQuantityChanges()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopProductQuantityChange', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopProductQuantityChanges0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopProductQuantityChange', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopQuantityNoticeEmails()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopQuantityNoticeEmail', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopQuantityNoticeEmails0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopQuantityNoticeEmail', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopStores()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopStore', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopStores0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopStore', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopTaxes()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopTax', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopTaxes0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopTax', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopTaxRates()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopTaxRate', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopTaxRates0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopTaxRate', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopTypePrices()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopTypePrice', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopTypePrices0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopTypePrice', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopUserAccounts()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopUserAccount', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopUserAccounts0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopUserAccount', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopUserAccounts1()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopUserAccount', ['user_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCurrencyCodes()
    {
        return $this->hasMany($this->called_class_namespace . '\MoneyCurrency', ['code' => 'currency_code'])->viaTable('shop_user_account', ['user_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopUserTransacts()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopUserTransact', ['cms_user_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopUserTransacts0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopUserTransact', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopUserTransacts1()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopUserTransact', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopVats()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopVat', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopVats0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopVat', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopViewedProducts()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopViewedProduct', ['created_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopViewedProducts0()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopViewedProduct', ['updated_by' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSsUserVotes()
    {
        return $this->hasMany($this->called_class_namespace . '\SsUserVote', ['cms_user_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\CmsUserQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CmsUserQuery(get_called_class());
    }
}
