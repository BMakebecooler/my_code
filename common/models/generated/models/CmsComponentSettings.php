<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "cms_component_settings".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $component Component
 * @property string $value Value
 * @property string $site_code Site Code
 * @property integer $user_id User ID
 * @property string $lang_code Lang Code
 * @property string $namespace Namespace
 *
     * @property CmsLang $langCode
     * @property CmsSite $siteCode
     * @property CmsUser $user
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
    */
class CmsComponentSettings extends \common\ActiveRecord
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
        return 'cms_component_settings';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'user_id'], 'integer'],
            [['value'], 'string'],
            [['component'], 'string', 'max' => 255],
            [['site_code'], 'string', 'max' => 15],
            [['lang_code'], 'string', 'max' => 5],
            [['namespace'], 'string', 'max' => 50],
            [['lang_code'], 'exist', 'skipOnError' => true, 'targetClass' => CmsLang::className(), 'targetAttribute' => ['lang_code' => 'code']],
            [['site_code'], 'exist', 'skipOnError' => true, 'targetClass' => CmsSite::className(), 'targetAttribute' => ['site_code' => 'code']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['user_id' => 'id']],
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
            'component' => 'Component',
            'value' => 'Value',
            'site_code' => 'Site Code',
            'user_id' => 'User ID',
            'lang_code' => 'Lang Code',
            'namespace' => 'Namespace',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getLangCode()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsLang', ['code' => 'lang_code']);
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
    public function getUser()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'user_id']);
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
     * @return \common\models\query\CmsComponentSettingsQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CmsComponentSettingsQuery(get_called_class());
    }
}
