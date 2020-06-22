<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "segment_product_card".
 *
 * @property integer $id ID
 * @property integer $segment_id Segment ID
 * @property integer $lot_id Lot ID
 * @property integer $card_id Card ID
 * @property integer $sort Sort
 * @property integer $qty Qty
 * @property integer $first First
 *
     * @property CmsContentElement $card
     * @property CmsContentElement $lot
     * @property Segment $segment
    */
class SegmentProductCard extends \common\ActiveRecord
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
        return 'segment_product_card';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['segment_id', 'lot_id', 'card_id', 'sort', 'qty', 'first'], 'integer'],
            [['lot_id', 'card_id', 'segment_id'], 'unique', 'targetAttribute' => ['lot_id', 'card_id', 'segment_id'], 'message' => 'The combination of Segment ID, Lot ID and Card ID has already been taken.'],
            [['card_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['card_id' => 'id']],
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
            'card_id' => 'Card ID',
            'sort' => 'Sort',
            'qty' => 'Qty',
            'first' => 'First',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCard()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContentElement', ['id' => 'card_id']);
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
     * @return \common\models\query\SegmentProductCardQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SegmentProductCardQuery(get_called_class());
    }
}
