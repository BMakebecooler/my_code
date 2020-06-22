<?php

namespace common\models\generated\models;


/**
 * This is the model class for table "buh_e_comm_dop".
 *
 * @property integer $id ID
 * @property integer $source_id Source ID
 * @property integer $product_id Product ID
 *
     * @property CmsContentElement $product
     * @property CmsContentElement $source
    */
class BuhECommDop extends \common\ActiveRecord
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
        return 'buh_e_comm_dop';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['source_id', 'product_id'], 'integer'],
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
     * @return \common\models\query\BuhECommDopQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\BuhECommDopQuery(get_called_class());
    }
}
