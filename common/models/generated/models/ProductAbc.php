<?php

namespace common\models\generated\models;


/**
 * This is the model class for table "product_abc".
 *
 * @property integer $id ID
 * @property string $guid Guid
 * @property integer $order Order
 * @property string $code Code
 * @property integer $type_id Type ID
*/
class ProductAbc extends \common\ActiveRecord
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
        return 'product_abc';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['order', 'type_id'], 'integer'],
            [['guid', 'code'], 'string', 'max' => 255],
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
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ProductAbcQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ProductAbcQuery(get_called_class());
    }
}
