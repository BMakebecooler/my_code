<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "segment_products".
 *
 * @property integer $id ID
 * @property integer $product_id Product ID
 * @property integer $segment_id Segment ID
 * @property integer $sort Sort
 * @property integer $qty Qty
 * @property integer $first First
*/
class SegmentProducts extends \common\ActiveRecord
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
        return 'segment_products';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['product_id', 'segment_id'], 'required'],
            [['product_id', 'segment_id', 'sort', 'qty', 'first'], 'integer'],
            [['segment_id', 'product_id'], 'unique', 'targetAttribute' => ['segment_id', 'product_id'], 'message' => 'The combination of Product ID and Segment ID has already been taken.'],
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
            'sort' => 'Sort',
            'qty' => 'Qty',
            'first' => 'First',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SegmentProductsQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SegmentProductsQuery(get_called_class());
    }
}
