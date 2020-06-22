<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_user_account".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $user_id User ID
 * @property string $current_budget Current Budget
 * @property string $currency_code Currency Code
 * @property string $locked Locked
 * @property integer $locked_at Locked At
 * @property string $notes Notes
 *
     * @property CmsUser $createdBy
     * @property MoneyCurrency $currencyCode
     * @property CmsUser $updatedBy
     * @property CmsUser $user
    */
class ShopUserAccount extends \common\ActiveRecord
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
        return 'shop_user_account';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'user_id', 'locked_at'], 'integer'],
            [['user_id', 'currency_code'], 'required'],
            [['current_budget'], 'number'],
            [['notes'], 'string'],
            [['currency_code'], 'string', 'max' => 3],
            [['locked'], 'string', 'max' => 1],
            [['currency_code', 'user_id'], 'unique', 'targetAttribute' => ['currency_code', 'user_id'], 'message' => 'The combination of User ID and Currency Code has already been taken.'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['currency_code'], 'exist', 'skipOnError' => true, 'targetClass' => MoneyCurrency::className(), 'targetAttribute' => ['currency_code' => 'code']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['updated_by' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['user_id' => 'id']],
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
            'user_id' => 'User ID',
            'current_budget' => 'Current Budget',
            'currency_code' => 'Currency Code',
            'locked' => 'Locked',
            'locked_at' => 'Locked At',
            'notes' => 'Notes',
            ];
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
    public function getCurrencyCode()
    {
        return $this->hasOne($this->called_class_namespace . '\MoneyCurrency', ['code' => 'currency_code']);
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
    public function getUser()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'user_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ShopUserAccountQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopUserAccountQuery(get_called_class());
    }
}
