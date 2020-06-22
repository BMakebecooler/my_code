<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_user_transact".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $cms_user_id Cms User ID
 * @property integer $shop_order_id Shop Order ID
 * @property string $amount Amount
 * @property string $currency_code Currency Code
 * @property string $debit Debit
 * @property string $description Description
 * @property string $notes Notes
 *
     * @property CmsUser $cmsUser
     * @property MoneyCurrency $currencyCode
     * @property ShopOrder $shopOrder
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
    */
class ShopUserTransact extends \common\ActiveRecord
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
        return 'shop_user_transact';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'cms_user_id', 'shop_order_id'], 'integer'],
            [['cms_user_id', 'currency_code', 'description'], 'required'],
            [['amount'], 'number'],
            [['notes'], 'string'],
            [['currency_code'], 'string', 'max' => 3],
            [['debit'], 'string', 'max' => 1],
            [['description'], 'string', 'max' => 255],
            [['cms_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['cms_user_id' => 'id']],
            [['currency_code'], 'exist', 'skipOnError' => true, 'targetClass' => MoneyCurrency::className(), 'targetAttribute' => ['currency_code' => 'code']],
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
            'cms_user_id' => 'Cms User ID',
            'shop_order_id' => 'Shop Order ID',
            'amount' => 'Amount',
            'currency_code' => 'Currency Code',
            'debit' => 'Debit',
            'description' => 'Description',
            'notes' => 'Notes',
            ];
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
    public function getCurrencyCode()
    {
        return $this->hasOne($this->called_class_namespace . '\MoneyCurrency', ['code' => 'currency_code']);
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
     * @return \common\models\query\ShopUserTransactQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopUserTransactQuery(get_called_class());
    }
}
