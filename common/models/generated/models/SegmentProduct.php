<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "segment_products".
 *
 * @property integer $id
 * @property integer $product_id
 * @property integer $segment_id
 *
 * @property CmsContentElement $product
 * @property Segment $segment
 */
class SegmentProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'segment_products';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'segment_id'], 'required'],
            [['product_id', 'segment_id'], 'integer'],
            [['product_id', 'segment_id'], 'unique', 'targetAttribute' => ['product_id', 'segment_id'], 'message' => 'The combination of Product ID and Segment ID has already been taken.'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['product_id' => 'id']],
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
            'product_id' => 'Product ID',
            'segment_id' => 'Segment ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(CmsContentElement::className(), ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSegment()
    {
        return $this->hasOne(Segment::className(), ['id' => 'segment_id']);
    }
}
