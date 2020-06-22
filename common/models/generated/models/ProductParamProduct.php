<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "product_param_product".
 *
 * @property integer $id ID
 * @property integer $product_id Product ID
 * @property integer $product_param_id Product Param ID
 * @property integer $card_id Card ID
 * @property integer $lot_id Lot ID
 *
     * @property CmsContentElement $product
     * @property ProductParam $productParam
    */
class ProductParamProduct extends \common\ActiveRecord
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
        return 'product_param_product';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['product_id', 'product_param_id'], 'required'],
            [['product_id', 'product_param_id', 'card_id', 'lot_id'], 'integer'],
            [['product_id', 'product_param_id'], 'unique', 'targetAttribute' => ['product_id', 'product_param_id'], 'message' => 'The combination of Product ID and Product Param ID has already been taken.'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['product_param_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductParam::className(), 'targetAttribute' => ['product_param_id' => 'id']],
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
            'product_param_id' => 'Product Param ID',
            'card_id' => 'Card ID',
            'lot_id' => 'Lot ID',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getProduct()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContentElement', ['id' => 'product_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getProductParam()
    {
        return $this->hasOne($this->called_class_namespace . '\ProductParam', ['id' => 'product_param_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ProductParamProductQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ProductParamProductQuery(get_called_class());
    }
}
