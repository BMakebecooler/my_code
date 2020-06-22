<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "form2_form_send".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $processed_by Processed By
 * @property integer $processed_at Processed At
 * @property string $data_values Data Values
 * @property string $data_labels Data Labels
 * @property string $emails Emails
 * @property string $phones Phones
 * @property string $user_ids User Ids
 * @property string $email_message Email Message
 * @property string $phone_message Phone Message
 * @property integer $status Status
 * @property integer $form_id Form ID
 * @property string $ip Ip
 * @property string $page_url Page Url
 * @property string $data_server Data Server
 * @property string $data_session Data Session
 * @property string $data_cookie Data Cookie
 * @property string $data_request Data Request
 * @property string $additional_data Additional Data
 * @property string $site_code Site Code
 * @property string $comment Comment
 *
     * @property CmsUser $createdBy
     * @property Form2Form $form
     * @property CmsUser $processedBy
     * @property CmsSite $siteCode
     * @property CmsUser $updatedBy
     * @property Form2FormSendProperty[] $form2FormSendProperties
    */
class Form2FormSend extends \common\ActiveRecord
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
        return 'form2_form_send';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'processed_by', 'processed_at', 'status', 'form_id'], 'integer'],
            [['data_values', 'data_labels', 'emails', 'phones', 'user_ids', 'email_message', 'phone_message', 'data_server', 'data_session', 'data_cookie', 'data_request', 'additional_data', 'comment'], 'string'],
            [['ip'], 'string', 'max' => 32],
            [['page_url'], 'string', 'max' => 500],
            [['site_code'], 'string', 'max' => 15],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['form_id'], 'exist', 'skipOnError' => true, 'targetClass' => Form2Form::className(), 'targetAttribute' => ['form_id' => 'id']],
            [['processed_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['processed_by' => 'id']],
            [['site_code'], 'exist', 'skipOnError' => true, 'targetClass' => CmsSite::className(), 'targetAttribute' => ['site_code' => 'code']],
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
            'processed_by' => 'Processed By',
            'processed_at' => 'Processed At',
            'data_values' => 'Data Values',
            'data_labels' => 'Data Labels',
            'emails' => 'Emails',
            'phones' => 'Phones',
            'user_ids' => 'User Ids',
            'email_message' => 'Email Message',
            'phone_message' => 'Phone Message',
            'status' => 'Status',
            'form_id' => 'Form ID',
            'ip' => 'Ip',
            'page_url' => 'Page Url',
            'data_server' => 'Data Server',
            'data_session' => 'Data Session',
            'data_cookie' => 'Data Cookie',
            'data_request' => 'Data Request',
            'additional_data' => 'Additional Data',
            'site_code' => 'Site Code',
            'comment' => 'Comment',
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
    public function getForm()
    {
        return $this->hasOne($this->called_class_namespace . '\Form2Form', ['id' => 'form_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getProcessedBy()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'processed_by']);
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
    public function getUpdatedBy()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'updated_by']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getForm2FormSendProperties()
    {
        return $this->hasMany($this->called_class_namespace . '\Form2FormSendProperty', ['element_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\Form2FormSendQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\Form2FormSendQuery(get_called_class());
    }
}
