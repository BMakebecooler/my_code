<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "sms".
 *
 * @property integer $id ID
 * @property string $provider_sms_id Provider Sms ID
 * @property string $phone Phone
 * @property string $text Text
 * @property integer $type Type
 * @property integer $for_user_id For User ID
 * @property integer $status Status
 * @property integer $created_by Created By
 * @property string $created_at Created At
 * @property string $must_sent_at Must Sent At
 * @property string $check_status_at Check Status At
 * @property string $provider Provider
 * @property string $provider_answer Provider Answer
 * @property integer $fuser_id Fuser ID
 * @property string $ip Ip
*/
class Sms extends \common\ActiveRecord
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
        return 'sms';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['phone', 'text', 'created_at'], 'required'],
            [['text', 'provider_answer'], 'string'],
            [['type', 'for_user_id', 'status', 'created_by', 'fuser_id'], 'integer'],
            [['created_at', 'must_sent_at', 'check_status_at'], 'safe'],
            [['provider_sms_id'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 20],
            [['provider'], 'string', 'max' => 100],
            [['ip'], 'string', 'max' => 15],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'provider_sms_id' => 'Provider Sms ID',
            'phone' => 'Phone',
            'text' => 'Text',
            'type' => 'Type',
            'for_user_id' => 'For User ID',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'must_sent_at' => 'Must Sent At',
            'check_status_at' => 'Check Status At',
            'provider' => 'Provider',
            'provider_answer' => 'Provider Answer',
            'fuser_id' => 'Fuser ID',
            'ip' => 'Ip',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SmsQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SmsQuery(get_called_class());
    }
}
