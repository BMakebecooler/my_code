<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "cms_user_phone".
 *
 * @property integer $id ID
 * @property integer $user_id User ID
 * @property string $value Value
 * @property string $approved Approved
 * @property string $def Def
 * @property string $approved_key Approved Key
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $source Source
 * @property string $source_detail Source Detail
 *
     * @property CmsUser $user
    */
class CmsUserPhone extends \common\ActiveRecord
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
        return 'cms_user_phone';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['user_id', 'created_at', 'updated_at'], 'integer'],
            [['value'], 'required'],
            [['value', 'approved_key', 'source', 'source_detail'], 'string', 'max' => 255],
            [['approved', 'def'], 'string', 'max' => 1],
            [['value'], 'unique'],
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
            'user_id' => 'User ID',
            'value' => 'Value',
            'approved' => 'Approved',
            'def' => 'Def',
            'approved_key' => 'Approved Key',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'source' => 'Source',
            'source_detail' => 'Source Detail',
            ];
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
     * @return \common\models\query\CmsUserPhoneQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CmsUserPhoneQuery(get_called_class());
    }
}
