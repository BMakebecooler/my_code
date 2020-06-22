<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "cms_tree_image".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $storage_file_id Storage File ID
 * @property integer $tree_id Tree ID
 * @property integer $priority Priority
 *
     * @property CmsStorageFile $storageFile
     * @property CmsTree $tree
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
    */
class CmsTreeImage extends \common\ActiveRecord
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
        return 'cms_tree_image';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'storage_file_id', 'tree_id', 'priority'], 'integer'],
            [['storage_file_id', 'tree_id'], 'required'],
            [['storage_file_id', 'tree_id'], 'unique', 'targetAttribute' => ['storage_file_id', 'tree_id'], 'message' => 'The combination of Storage File ID and Tree ID has already been taken.'],
            [['storage_file_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsStorageFile::className(), 'targetAttribute' => ['storage_file_id' => 'id']],
            [['tree_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsTree::className(), 'targetAttribute' => ['tree_id' => 'id']],
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
            'tree_id' => 'Tree ID',
            'priority' => 'Priority',
            ];
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
    public function getTree()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsTree', ['id' => 'tree_id']);
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
     * @return \common\models\query\CmsTreeImageQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CmsTreeImageQuery(get_called_class());
    }
}
