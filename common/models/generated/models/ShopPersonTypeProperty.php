<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_person_type_property".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $name Name
 * @property string $code Code
 * @property string $active Active
 * @property integer $priority Priority
 * @property string $property_type Property Type
 * @property string $multiple Multiple
 * @property string $is_required Is Required
 * @property string $component Component
 * @property string $component_settings Component Settings
 * @property string $hint Hint
 * @property integer $shop_person_type_id Shop Person Type ID
 * @property string $is_order_location_delivery Is Order Location Delivery
 * @property string $is_order_location_tax Is Order Location Tax
 * @property string $is_order_postcode Is Order Postcode
 * @property string $is_user_email Is User Email
 * @property string $is_user_phone Is User Phone
 * @property string $is_user_username Is User Username
 * @property string $is_user_name Is User Name
 * @property string $is_buyer_name Is Buyer Name
 *
     * @property ShopBuyerProperty[] $shopBuyerProperties
     * @property ShopPersonType $shopPersonType
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
     * @property ShopPersonTypePropertyEnum[] $shopPersonTypePropertyEnums
    */
class ShopPersonTypeProperty extends \common\ActiveRecord
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
        return 'shop_person_type_property';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'priority', 'shop_person_type_id'], 'integer'],
            [['name', 'shop_person_type_id'], 'required'],
            [['component_settings'], 'string'],
            [['name', 'component', 'hint', 'is_order_location_delivery', 'is_order_location_tax', 'is_order_postcode', 'is_user_email', 'is_user_phone', 'is_user_username', 'is_user_name', 'is_buyer_name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 64],
            [['active', 'property_type', 'multiple', 'is_required'], 'string', 'max' => 1],
            [['shop_person_type_id', 'code'], 'unique', 'targetAttribute' => ['shop_person_type_id', 'code'], 'message' => 'The combination of Code and Shop Person Type ID has already been taken.'],
            [['shop_person_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopPersonType::className(), 'targetAttribute' => ['shop_person_type_id' => 'id']],
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
            'code' => 'Code',
            'active' => 'Active',
            'priority' => 'Priority',
            'property_type' => 'Property Type',
            'multiple' => 'Multiple',
            'is_required' => 'Is Required',
            'component' => 'Component',
            'component_settings' => 'Component Settings',
            'hint' => 'Hint',
            'shop_person_type_id' => 'Shop Person Type ID',
            'is_order_location_delivery' => 'Is Order Location Delivery',
            'is_order_location_tax' => 'Is Order Location Tax',
            'is_order_postcode' => 'Is Order Postcode',
            'is_user_email' => 'Is User Email',
            'is_user_phone' => 'Is User Phone',
            'is_user_username' => 'Is User Username',
            'is_user_name' => 'Is User Name',
            'is_buyer_name' => 'Is Buyer Name',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopBuyerProperties()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopBuyerProperty', ['property_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopPersonType()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopPersonType', ['id' => 'shop_person_type_id']);
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
    public function getShopPersonTypePropertyEnums()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopPersonTypePropertyEnum', ['property_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ShopPersonTypePropertyQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopPersonTypePropertyQuery(get_called_class());
    }
}
