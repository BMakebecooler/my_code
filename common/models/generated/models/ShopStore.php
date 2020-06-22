<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_store".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $name Name
 * @property string $active Active
 * @property string $address Address
 * @property string $description Description
 * @property string $gps_n Gps N
 * @property string $gps_s Gps S
 * @property integer $image_id Image ID
 * @property integer $location_id Location ID
 * @property string $phone Phone
 * @property string $schedule Schedule
 * @property string $xml_id Xml ID
 * @property integer $priority Priority
 * @property string $email Email
 * @property string $issuing_center Issuing Center
 * @property string $shipping_center Shipping Center
 * @property string $site_code Site Code
 *
     * @property CmsStorageFile $image
     * @property KladrLocation $location
     * @property CmsSite $siteCode
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
    */
class ShopStore extends \common\ActiveRecord
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
        return 'shop_store';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'image_id', 'location_id', 'priority'], 'integer'],
            [['name', 'address'], 'required'],
            [['description'], 'string'],
            [['name', 'address', 'phone', 'schedule', 'xml_id', 'email'], 'string', 'max' => 255],
            [['active', 'issuing_center', 'shipping_center'], 'string', 'max' => 1],
            [['gps_n', 'gps_s', 'site_code'], 'string', 'max' => 15],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsStorageFile::className(), 'targetAttribute' => ['image_id' => 'id']],
            [['location_id'], 'exist', 'skipOnError' => true, 'targetClass' => KladrLocation::className(), 'targetAttribute' => ['location_id' => 'id']],
            [['site_code'], 'exist', 'skipOnError' => true, 'targetClass' => CmsSite::className(), 'targetAttribute' => ['site_code' => 'code']],
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
            'active' => 'Active',
            'address' => 'Address',
            'description' => 'Description',
            'gps_n' => 'Gps N',
            'gps_s' => 'Gps S',
            'image_id' => 'Image ID',
            'location_id' => 'Location ID',
            'phone' => 'Phone',
            'schedule' => 'Schedule',
            'xml_id' => 'Xml ID',
            'priority' => 'Priority',
            'email' => 'Email',
            'issuing_center' => 'Issuing Center',
            'shipping_center' => 'Shipping Center',
            'site_code' => 'Site Code',
            ];
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
    public function getLocation()
    {
        return $this->hasOne($this->called_class_namespace . '\KladrLocation', ['id' => 'location_id']);
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
     * @inheritdoc
     * @return \common\models\query\ShopStoreQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopStoreQuery(get_called_class());
    }
}
