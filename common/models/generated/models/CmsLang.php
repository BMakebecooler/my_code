<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "cms_lang".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $active Active
 * @property string $def Def
 * @property integer $priority Priority
 * @property string $code Code
 * @property string $name Name
 * @property string $description Description
 * @property integer $image_id Image ID
 *
     * @property CmsComponentSettings[] $cmsComponentSettings
     * @property CmsStorageFile $image
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
    */
class CmsLang extends \common\ActiveRecord
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
        return 'cms_lang';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'priority', 'image_id'], 'integer'],
            [['code', 'name'], 'required'],
            [['active', 'def'], 'string', 'max' => 1],
            [['code'], 'string', 'max' => 5],
            [['name', 'description'], 'string', 'max' => 255],
            [['code'], 'unique'],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsStorageFile::className(), 'targetAttribute' => ['image_id' => 'id']],
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
            'active' => 'Active',
            'def' => 'Def',
            'priority' => 'Priority',
            'code' => 'Code',
            'name' => 'Name',
            'description' => 'Description',
            'image_id' => 'Image ID',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsComponentSettings()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsComponentSettings', ['lang_code' => 'code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getImage()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsStorageFile', ['id' => 'image_id']);
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
     * @return \common\models\query\CmsLangQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CmsLangQuery(get_called_class());
    }
}
