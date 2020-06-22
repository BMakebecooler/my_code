<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "reviews2_message".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $published_at Published At
 * @property integer $processed_by Processed By
 * @property integer $processed_at Processed At
 * @property integer $element_id Element ID
 * @property integer $content_id Content ID
 * @property string $dignity Dignity
 * @property string $disadvantages Disadvantages
 * @property string $comments Comments
 * @property integer $rating Rating
 * @property integer $status Status
 * @property string $ip Ip
 * @property string $page_url Page Url
 * @property string $data_server Data Server
 * @property string $data_session Data Session
 * @property string $data_cookie Data Cookie
 * @property string $data_request Data Request
 * @property string $site_code Site Code
 * @property string $user_name User Name
 * @property string $user_email User Email
 * @property string $user_phone User Phone
 * @property string $user_city User City
 *
     * @property CmsContent $content
     * @property CmsUser $createdBy
     * @property CmsContentElement $element
     * @property CmsUser $processedBy
     * @property CmsSite $siteCode
     * @property CmsUser $updatedBy
    */
class Reviews2Message extends \common\ActiveRecord
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
        return 'reviews2_message';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'published_at', 'processed_by', 'processed_at', 'element_id', 'content_id', 'rating', 'status'], 'integer'],
            [['element_id', 'rating'], 'required'],
            [['dignity', 'disadvantages', 'comments', 'page_url', 'data_server', 'data_session', 'data_cookie', 'data_request'], 'string'],
            [['ip'], 'string', 'max' => 32],
            [['site_code'], 'string', 'max' => 15],
            [['user_name', 'user_email', 'user_phone', 'user_city'], 'string', 'max' => 255],
            [['content_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContent::className(), 'targetAttribute' => ['content_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['element_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['element_id' => 'id']],
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
            'published_at' => 'Published At',
            'processed_by' => 'Processed By',
            'processed_at' => 'Processed At',
            'element_id' => 'Element ID',
            'content_id' => 'Content ID',
            'dignity' => 'Dignity',
            'disadvantages' => 'Disadvantages',
            'comments' => 'Comments',
            'rating' => 'Rating',
            'status' => 'Status',
            'ip' => 'Ip',
            'page_url' => 'Page Url',
            'data_server' => 'Data Server',
            'data_session' => 'Data Session',
            'data_cookie' => 'Data Cookie',
            'data_request' => 'Data Request',
            'site_code' => 'Site Code',
            'user_name' => 'User Name',
            'user_email' => 'User Email',
            'user_phone' => 'User Phone',
            'user_city' => 'User City',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getContent()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContent', ['id' => 'content_id']);
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
    public function getElement()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContentElement', ['id' => 'element_id']);
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
     * @inheritdoc
     * @return \common\models\query\Reviews2MessageQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\Reviews2MessageQuery(get_called_class());
    }
}
