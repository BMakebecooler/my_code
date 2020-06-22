<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "product_param_type".
 *
 * @property integer $id ID
 * @property string $name Name
 * @property string $guid Guid
 * @property string $code Code
 * @property integer $sort Sort
 * @property integer $active Active
 *
     * @property ProductParam[] $productParams
    */
class ProductParamType extends \common\ActiveRecord
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
        return 'product_param_type';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['name', 'guid'], 'required'],
            [['sort', 'active'], 'integer'],
            [['name', 'guid', 'code'], 'string', 'max' => 255],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'guid' => 'Guid',
            'code' => 'Code',
            'sort' => 'Sort',
            'active' => 'Active',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getProductParams()
    {
        return $this->hasMany($this->called_class_namespace . '\ProductParam', ['type_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ProductParamTypeQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ProductParamTypeQuery(get_called_class());
    }
}
