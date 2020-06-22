<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "cms_content_element_faq".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $element_id Element ID
 * @property string $username Username
 * @property string $email Email
 * @property string $question Question
 * @property string $answer Answer
 * @property integer $like Like
 * @property integer $dislike Dislike
 * @property integer $status Status
 * @property string $user_ip User Ip
 * @property string $url Url
 * @property integer $editor_lastview_at Editor Lastview At
 * @property integer $sent_service_at Sent Service At
 * @property integer $sent_buyer_at Sent Buyer At
 * @property integer $published_at Published At
 * @property string $buyer_answer Buyer Answer
 * @property string $service_answer Service Answer
 * @property string $copyright_answer Copyright Answer
 * @property integer $is_sms_notification Is Sms Notification
 * @property integer $fuser_id Fuser ID
 * @property string $phone Phone
 * @property integer $type Type
 * @property integer $parent_id Parent ID
 *
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
    */
class CmsContentElementFaq extends \common\ActiveRecord
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
        return 'cms_content_element_faq';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'element_id', 'like', 'dislike', 'status', 'editor_lastview_at', 'sent_service_at', 'sent_buyer_at', 'published_at', 'is_sms_notification', 'fuser_id', 'type', 'parent_id'], 'integer'],
            [['element_id'], 'required'],
            [['question', 'answer', 'buyer_answer', 'service_answer', 'copyright_answer'], 'string'],
            [['username', 'email'], 'string', 'max' => 128],
            [['user_ip'], 'string', 'max' => 15],
            [['url'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 20],
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
            'element_id' => 'Element ID',
            'username' => 'Username',
            'email' => 'Email',
            'question' => 'Question',
            'answer' => 'Answer',
            'like' => 'Like',
            'dislike' => 'Dislike',
            'status' => 'Status',
            'user_ip' => 'User Ip',
            'url' => 'Url',
            'editor_lastview_at' => 'Editor Lastview At',
            'sent_service_at' => 'Sent Service At',
            'sent_buyer_at' => 'Sent Buyer At',
            'published_at' => 'Published At',
            'buyer_answer' => 'Buyer Answer',
            'service_answer' => 'Service Answer',
            'copyright_answer' => 'Copyright Answer',
            'is_sms_notification' => 'Is Sms Notification',
            'fuser_id' => 'Fuser ID',
            'phone' => 'Phone',
            'type' => 'Type',
            'parent_id' => 'Parent ID',
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
    public function getUpdatedBy()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'updated_by']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\CmsContentElementFaqQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CmsContentElementFaqQuery(get_called_class());
    }
}
