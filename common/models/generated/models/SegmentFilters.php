<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "segment_filters".
 *
 * @property integer $id ID
 * @property integer $id_category Id Category
 * @property string $name Name
 * @property string $description Description
 * @property string $field Field
 * @property string $operand Operand
 * @property string $table Table
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 *
     * @property SegmentFilterCategories $idCategory
     * @property SegmentSegmentFilters[] $segmentSegmentFilters
    */
class SegmentFilters extends \common\ActiveRecord
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
        return 'segment_filters';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['id_category', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['name', 'field', 'operand', 'table'], 'required'],
            [['description'], 'string'],
            [['name', 'field', 'operand', 'table'], 'string', 'max' => 255],
            [['id_category'], 'exist', 'skipOnError' => true, 'targetClass' => SegmentFilterCategories::className(), 'targetAttribute' => ['id_category' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_category' => 'Id Category',
            'name' => 'Name',
            'description' => 'Description',
            'field' => 'Field',
            'operand' => 'Operand',
            'table' => 'Table',
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
    public function getIdCategory()
    {
        return $this->hasOne($this->called_class_namespace . '\SegmentFilterCategories', ['id' => 'id_category']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSegmentSegmentFilters()
    {
        return $this->hasMany($this->called_class_namespace . '\SegmentSegmentFilters', ['filter_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SegmentFiltersQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SegmentFiltersQuery(get_called_class());
    }
}
