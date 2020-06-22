<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "segment_lots_disable".
 *
 * @property integer $id ID
 * @property integer $segment_id Segment ID
 * @property integer $lot_id Lot ID
 *
     * @property CmsContentElement $lot
     * @property Segment $segment
    */
class SegmentLotsDisable extends \common\ActiveRecord
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
        return 'segment_lots_disable';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['segment_id', 'lot_id'], 'integer'],
            [['lot_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['lot_id' => 'id']],
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
            'lot_id' => 'Lot ID',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getLot()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContentElement', ['id' => 'lot_id']);
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
     * @return \common\models\query\SegmentLotsDisableQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SegmentLotsDisableQuery(get_called_class());
    }
}
