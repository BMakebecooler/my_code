<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_shop_discount_entity".
 *
 * @property integer $id ID
 * @property string $name Name
 * @property string $class Class
 *
     * @property SsShopDiscountConfiguration[] $ssShopDiscountConfigurations
    */
class SsShopDiscountEntity extends \common\ActiveRecord
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
        return 'ss_shop_discount_entity';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['name', 'class'], 'required'],
            [['name', 'class'], 'string', 'max' => 255],
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
            'class' => 'Class',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSsShopDiscountConfigurations()
    {
        return $this->hasMany($this->called_class_namespace . '\SsShopDiscountConfiguration', ['shop_discount_entity_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsShopDiscountEntityQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsShopDiscountEntityQuery(get_called_class());
    }
}
