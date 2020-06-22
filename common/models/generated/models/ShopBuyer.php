<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_buyer".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $name Name
 * @property integer $cms_user_id Cms User ID
 * @property integer $shop_person_type_id Shop Person Type ID
 *
     * @property ShopPersonType $shopPersonType
     * @property CmsUser $cmsUser
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
     * @property ShopBuyerProperty[] $shopBuyerProperties
     * @property ShopFuser[] $shopFusers
     * @property ShopOrder[] $shopOrders
    */
class ShopBuyer extends \common\ActiveRecord
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
        return 'shop_buyer';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'cms_user_id', 'shop_person_type_id'], 'integer'],
            [['name', 'shop_person_type_id'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['shop_person_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopPersonType::className(), 'targetAttribute' => ['shop_person_type_id' => 'id']],
            [['cms_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['cms_user_id' => 'id']],
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
            'cms_user_id' => 'Cms User ID',
            'shop_person_type_id' => 'Shop Person Type ID',
            ];
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
    public function getCmsUser()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'cms_user_id']);
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
    public function getShopBuyerProperties()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopBuyerProperty', ['element_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopFusers()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopFuser', ['buyer_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopOrders()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopOrder', ['buyer_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ShopBuyerQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopBuyerQuery(get_called_class());
    }
}
