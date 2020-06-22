<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "segment_filter_categories".
 *
 * @property integer $id ID
 * @property string $name Name
 * @property string $description Description
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 *
     * @property SegmentFilters[] $segmentFilters
    */
class SegmentFilterCategories extends \common\ActiveRecord
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
            'timestamp' => \yii\behaviors\TimestampBehavior::class,
            'author' => \yii\behaviors\BlameableBehavior::class,
        ];
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'segment_filter_categories';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['description'], 'string'],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSegmentFilters()
    {
        return $this->hasMany($this->called_class_namespace . '\SegmentFilters', ['id_category' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SegmentFilterCategoriesQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SegmentFilterCategoriesQuery(get_called_class());
    }
}
