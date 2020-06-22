<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_products_segments".
 *
 * @property integer $id ID
 * @property integer $product_id Product ID
 * @property integer $bitrix_id Bitrix ID
 * @property string $segment Segment
 * @property integer $begin_datetime Begin Datetime
 * @property integer $end_datetime End Datetime
 * @property integer $file_id File ID
 *
     * @property SsSegmentsFiles $file
    */
class SsProductsSegments extends \common\ActiveRecord
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
        return 'ss_products_segments';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['product_id', 'bitrix_id', 'begin_datetime', 'end_datetime', 'file_id'], 'integer'],
            [['segment'], 'string', 'max' => 30],
            [['product_id', 'file_id'], 'unique', 'targetAttribute' => ['product_id', 'file_id'], 'message' => 'The combination of Product ID and File ID has already been taken.'],
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => SsSegmentsFiles::className(), 'targetAttribute' => ['file_id' => 'id']],
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
            'bitrix_id' => 'Bitrix ID',
            'segment' => 'Segment',
            'begin_datetime' => 'Begin Datetime',
            'end_datetime' => 'End Datetime',
            'file_id' => 'File ID',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getFile()
    {
        return $this->hasOne($this->called_class_namespace . '\SsSegmentsFiles', ['id' => 'file_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsProductsSegmentsQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsProductsSegmentsQuery(get_called_class());
    }
}
