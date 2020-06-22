<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_shop_discount_logic".
 *
 * @property integer $id ID
 * @property integer $shop_discount_id Shop Discount ID
 * @property string $logic_type Logic Type
 * @property string $value Value
 * @property string $discount_type Discount Type
 * @property string $discount_value Discount Value
 *
     * @property ShopDiscount $shopDiscount
    */
class SsShopDiscountLogic extends \common\ActiveRecord
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
        return 'ss_shop_discount_logic';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['shop_discount_id', 'logic_type', 'value', 'discount_type', 'discount_value'], 'required'],
            [['shop_discount_id'], 'integer'],
            [['value', 'discount_value'], 'number'],
            [['logic_type', 'discount_type'], 'string', 'max' => 1],
            [['shop_discount_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopDiscount::className(), 'targetAttribute' => ['shop_discount_id' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shop_discount_id' => 'Shop Discount ID',
            'logic_type' => 'Logic Type',
            'value' => 'Value',
            'discount_type' => 'Discount Type',
            'discount_value' => 'Discount Value',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDiscount()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopDiscount', ['id' => 'shop_discount_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsShopDiscountLogicQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsShopDiscountLogicQuery(get_called_class());
    }
}
