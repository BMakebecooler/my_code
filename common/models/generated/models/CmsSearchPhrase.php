<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "cms_search_phrase".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $phrase Phrase
 * @property integer $result_count Result Count
 * @property integer $pages Pages
 * @property string $ip Ip
 * @property string $session_id Session ID
 * @property string $site_code Site Code
 * @property string $data_server Data Server
 * @property string $data_session Data Session
 * @property string $data_cookie Data Cookie
 * @property string $data_request Data Request
 *
     * @property CmsUser $createdBy
     * @property CmsSite $siteCode
     * @property CmsUser $updatedBy
    */
class CmsSearchPhrase extends \common\ActiveRecord
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
        return 'cms_search_phrase';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'result_count', 'pages'], 'integer'],
            [['data_server', 'data_session', 'data_cookie', 'data_request'], 'string'],
            [['phrase'], 'string', 'max' => 255],
            [['ip', 'session_id'], 'string', 'max' => 32],
            [['site_code'], 'string', 'max' => 15],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
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
            'phrase' => 'Phrase',
            'result_count' => 'Result Count',
            'pages' => 'Pages',
            'ip' => 'Ip',
            'session_id' => 'Session ID',
            'site_code' => 'Site Code',
            'data_server' => 'Data Server',
            'data_session' => 'Data Session',
            'data_cookie' => 'Data Cookie',
            'data_request' => 'Data Request',
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
     * @return \common\models\query\CmsSearchPhraseQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CmsSearchPhraseQuery(get_called_class());
    }
}
