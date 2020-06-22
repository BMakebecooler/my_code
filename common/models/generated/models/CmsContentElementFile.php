<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "cms_content_element_file".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $storage_file_id Storage File ID
 * @property integer $content_element_id Content Element ID
 * @property integer $priority Priority
 *
     * @property CmsContentElement $contentElement
     * @property CmsStorageFile $storageFile
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
    */
class CmsContentElementFile extends \common\ActiveRecord
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
        return 'cms_content_element_file';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'storage_file_id', 'content_element_id', 'priority'], 'integer'],
            [['storage_file_id', 'content_element_id'], 'required'],
            [['storage_file_id', 'content_element_id'], 'unique', 'targetAttribute' => ['storage_file_id', 'content_element_id'], 'message' => 'The combination of Storage File ID and Content Element ID has already been taken.'],
            [['content_element_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['content_element_id' => 'id']],
            [['storage_file_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsStorageFile::className(), 'targetAttribute' => ['storage_file_id' => 'id']],
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
            'storage_file_id' => 'Storage File ID',
            'content_element_id' => 'Content Element ID',
            'priority' => 'Priority',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getContentElement()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContentElement', ['id' => 'content_element_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getStorageFile()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsStorageFile', ['id' => 'storage_file_id']);
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
     * @return \common\models\query\CmsContentElementFileQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CmsContentElementFileQuery(get_called_class());
    }
}
