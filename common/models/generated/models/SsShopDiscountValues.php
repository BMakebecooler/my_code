<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_shop_discount_values".
 *
 * @property integer $id ID
 * @property integer $shop_discount_configuration_id Shop Discount Configuration ID
 * @property integer $value Value
 *
     * @property SsShopDiscountConfiguration $shopDiscountConfiguration
    */
class SsShopDiscountValues extends \common\ActiveRecord
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
        return 'ss_shop_discount_values';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['shop_discount_configuration_id', 'value'], 'required'],
            [['shop_discount_configuration_id', 'value'], 'integer'],
            [['shop_discount_configuration_id'], 'exist', 'skipOnError' => true, 'targetClass' => SsShopDiscountConfiguration::className(), 'targetAttribute' => ['shop_discount_configuration_id' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shop_discount_configuration_id' => 'Shop Discount Configuration ID',
            'value' => 'Value',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDiscountConfiguration()
    {
        return $this->hasOne($this->called_class_namespace . '\SsShopDiscountConfiguration', ['id' => 'shop_discount_configuration_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsShopDiscountValuesQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsShopDiscountValuesQuery(get_called_class());
    }
}
