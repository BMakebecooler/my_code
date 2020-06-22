<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "segment_segment_filters".
 *
 * @property integer $id ID
 * @property integer $segment_id Segment ID
 * @property integer $filter_id Filter ID
 * @property string $value Value
 *
     * @property SegmentFilters $filter
     * @property Segment $segment
    */
class SegmentSegmentFilters extends \common\ActiveRecord
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
    public static function tableName()
    {
        return 'segment_segment_filters';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['segment_id', 'filter_id', 'value'], 'required'],
            [['segment_id', 'filter_id'], 'integer'],
            [['value'], 'string', 'max' => 255],
            [['filter_id'], 'exist', 'skipOnError' => true, 'targetClass' => SegmentFilters::className(), 'targetAttribute' => ['filter_id' => 'id']],
            [['segment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Segment::className(), 'targetAttribute' => ['segment_id' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'segment_id' => 'Segment ID',
            'filter_id' => 'Filter ID',
            'value' => 'Value',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getFilter()
    {
        return $this->hasOne($this->called_class_namespace . '\SegmentFilters', ['id' => 'filter_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSegment()
    {
        return $this->hasOne($this->called_class_namespace . '\Segment', ['id' => 'segment_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SegmentSegmentFiltersQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SegmentSegmentFiltersQuery(get_called_class());
    }
}
