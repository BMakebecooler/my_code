<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "buh_e_comm_abc".
 *
 * @property integer $id ID
 * @property string $guid Guid
 * @property integer $order Order
 * @property string $code Code
 * @property integer $type_id Type ID
 * @property integer $product_id Product ID
 * @property string $addition Addition
 *
     * @property CmsContentElement $product
    */
class BuhECommAbc extends \common\ActiveRecord
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
        return 'buh_e_comm_abc';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['order', 'type_id', 'product_id'], 'integer'],
            [['addition'], 'string'],
            [['guid', 'code'], 'string', 'max' => 255],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'guid' => 'Guid',
            'order' => 'Order',
            'code' => 'Code',
            'type_id' => 'Type ID',
            'product_id' => 'Product ID',
            'addition' => 'Addition',
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
     * @inheritdoc
     * @return \common\models\query\BuhECommAbcQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\BuhECommAbcQuery(get_called_class());
    }
}
