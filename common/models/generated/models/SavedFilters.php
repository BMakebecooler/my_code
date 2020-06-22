<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "saved_filters".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $image_id Image ID
 * @property string $name Name
 * @property string $code Code
 * @property string $description_short Description Short
 * @property string $description_full Description Full
 * @property string $description_short_type Description Short Type
 * @property string $description_full_type Description Full Type
 * @property string $component Component
 * @property string $component_settings Component Settings
 * @property integer $priority Priority
 * @property integer $is_active Is Active
 * @property string $meta_title Meta Title
 * @property string $meta_description Meta Description
 * @property string $meta_keywords Meta Keywords
 *
     * @property CmsUser $createdBy
     * @property CmsStorageFile $image
     * @property CmsUser $updatedBy
    */
class SavedFilters extends \common\ActiveRecord
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
        return 'saved_filters';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'image_id', 'priority', 'is_active'], 'integer'],
            [['name', 'code', 'component'], 'required'],
            [['description_short', 'description_full', 'component_settings', 'meta_description', 'meta_keywords'], 'string'],
            [['name', 'description_short_type', 'description_full_type', 'component'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 190],
            [['meta_title'], 'string', 'max' => 500],
            [['code'], 'unique'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsStorageFile::className(), 'targetAttribute' => ['image_id' => 'id']],
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
            'image_id' => 'Image ID',
            'name' => 'Name',
            'code' => 'Code',
            'description_short' => 'Description Short',
            'description_full' => 'Description Full',
            'description_short_type' => 'Description Short Type',
            'description_full_type' => 'Description Full Type',
            'component' => 'Component',
            'component_settings' => 'Component Settings',
            'priority' => 'Priority',
            'is_active' => 'Is Active',
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'meta_keywords' => 'Meta Keywords',
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
    public function getImage()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsStorageFile', ['id' => 'image_id']);
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
     * @return \common\models\query\SavedFiltersQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SavedFiltersQuery(get_called_class());
    }
}
