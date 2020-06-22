<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "auth_assignment".
 *
 * @property string $item_name Item Name
 * @property integer $user_id User ID
 * @property integer $created_at Created At
 *
     * @property AuthItem $itemName
     * @property CmsUser $user
    */
class AuthAssignment extends \common\ActiveRecord
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
        return 'auth_assignment';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['item_name', 'user_id'], 'required'],
            [['user_id', 'created_at'], 'integer'],
            [['item_name'], 'string', 'max' => 64],
            [['item_name'], 'exist', 'skipOnError' => true, 'targetClass' => AuthItem::className(), 'targetAttribute' => ['item_name' => 'name']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'item_name' => 'Item Name',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getItemName()
    {
        return $this->hasOne($this->called_class_namespace . '\AuthItem', ['name' => 'item_name']);
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
     * @return \common\models\query\AuthAssignmentQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\AuthAssignmentQuery(get_called_class());
    }
}
