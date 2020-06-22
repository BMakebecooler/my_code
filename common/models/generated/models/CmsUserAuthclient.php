<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "cms_user_authclient".
 *
 * @property integer $id ID
 * @property integer $user_id User ID
 * @property string $provider Provider
 * @property string $provider_identifier Provider Identifier
 * @property string $provider_data Provider Data
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 *
     * @property CmsUser $user
    */
class CmsUserAuthclient extends \common\ActiveRecord
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
        return 'cms_user_authclient';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'created_at', 'updated_at'], 'integer'],
            [['provider_data'], 'string'],
            [['provider'], 'string', 'max' => 50],
            [['provider_identifier'], 'string', 'max' => 100],
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
            'provider' => 'Provider',
            'provider_identifier' => 'Provider Identifier',
            'provider_data' => 'Provider Data',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
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
     * @return \common\models\query\CmsUserAuthclientQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CmsUserAuthclientQuery(get_called_class());
    }
}
