<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_order_change".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $shop_order_id Shop Order ID
 * @property string $type Type
 * @property string $data Data
 * @property string $status_code Status Code
 *
     * @property ShopOrderStatus $statusCode
     * @property ShopOrder $shopOrder
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
    */
class ShopOrderChange extends \common\ActiveRecord
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
        return 'shop_order_change';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'shop_order_id'], 'integer'],
            [['shop_order_id', 'type'], 'required'],
            [['data'], 'string'],
            [['type'], 'string', 'max' => 255],
            [['status_code'], 'string', 'max' => 1],
            [['status_code'], 'exist', 'skipOnError' => true, 'targetClass' => ShopOrderStatus::className(), 'targetAttribute' => ['status_code' => 'code']],
            [['shop_order_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopOrder::className(), 'targetAttribute' => ['shop_order_id' => 'id']],
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
            'shop_order_id' => 'Shop Order ID',
            'type' => 'Type',
            'data' => 'Data',
            'status_code' => 'Status Code',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getStatusCode()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopOrderStatus', ['code' => 'status_code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopOrder()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopOrder', ['id' => 'shop_order_id']);
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
     * @return \common\models\query\ShopOrderChangeQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopOrderChangeQuery(get_called_class());
    }
}
