<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "product_abc_addition".
 *
 * @property integer $id ID
 * @property integer $source_id Source ID
 * @property integer $product_id Product ID
 * @property integer $order Order
 *
     * @property CmsContentElement $product
     * @property CmsContentElement $source
    */
class ProductAbcAddition extends \common\ActiveRecord
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
        return 'product_abc_addition';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['source_id', 'product_id', 'order'], 'integer'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['source_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['source_id' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'source_id' => 'Source ID',
            'product_id' => 'Product ID',
            'order' => 'Order',
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
    public function getSource()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContentElement', ['id' => 'source_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ProductAbcAdditionQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ProductAbcAdditionQuery(get_called_class());
    }
}
