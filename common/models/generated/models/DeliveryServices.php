<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "delivery_services".
 *
 * @property integer $id ID
 * @property string $name Name
 * @property string $code Code
 * @property integer $isActive Is Active
 * @property integer $fixedCost Fixed Cost
 * @property string $info Info
 * @property string $terms Terms
 * @property integer $serviceShownFor Service Shown For
 * @property string $dateDb Date Db
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $delivery_id Delivery ID
 *
     * @property ShopDelivery $delivery
    */
class DeliveryServices extends \common\ActiveRecord
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
        ];
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'delivery_services';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['name', 'code', 'serviceShownFor', 'created_at', 'updated_at'], 'required'],
            [['isActive', 'fixedCost', 'serviceShownFor', 'created_at', 'updated_at', 'delivery_id'], 'integer'],
            [['dateDb'], 'safe'],
            [['name', 'code'], 'string', 'max' => 100],
            [['info', 'terms'], 'string', 'max' => 255],
            [['delivery_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopDelivery::className(), 'targetAttribute' => ['delivery_id' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'code' => 'Code',
            'isActive' => 'Is Active',
            'fixedCost' => 'Fixed Cost',
            'info' => 'Info',
            'terms' => 'Terms',
            'serviceShownFor' => 'Service Shown For',
            'dateDb' => 'Date Db',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'delivery_id' => 'Delivery ID',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getDelivery()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopDelivery', ['id' => 'delivery_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\DeliveryServicesQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\DeliveryServicesQuery(get_called_class());
    }
}
