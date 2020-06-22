<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_delivery2pay_system".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $pay_system_id Pay System ID
 * @property integer $delivery_id Delivery ID
 *
     * @property ShopDelivery $delivery
     * @property ShopPaySystem $paySystem
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
    */
class ShopDelivery2paySystem extends \common\ActiveRecord
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
        return 'shop_delivery2pay_system';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'pay_system_id', 'delivery_id'], 'integer'],
            [['pay_system_id', 'delivery_id'], 'required'],
            [['pay_system_id', 'delivery_id'], 'unique', 'targetAttribute' => ['pay_system_id', 'delivery_id'], 'message' => 'The combination of Pay System ID and Delivery ID has already been taken.'],
            [['delivery_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopDelivery::className(), 'targetAttribute' => ['delivery_id' => 'id']],
            [['pay_system_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopPaySystem::className(), 'targetAttribute' => ['pay_system_id' => 'id']],
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
            'pay_system_id' => 'Pay System ID',
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
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getPaySystem()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopPaySystem', ['id' => 'pay_system_id']);
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
     * @return \common\models\query\ShopDelivery2paySystemQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopDelivery2paySystemQuery(get_called_class());
    }
}
