<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_shop_discount_configuration".
 *
 * @property integer $id ID
 * @property integer $shop_discount_id Shop Discount ID
 * @property integer $shop_discount_entity_id Shop Discount Entity ID
 *
     * @property ShopDiscount $shopDiscount
     * @property SsShopDiscountEntity $shopDiscountEntity
     * @property SsShopDiscountValues[] $ssShopDiscountValues
    */
class SsShopDiscountConfiguration extends \common\ActiveRecord
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
        return 'ss_shop_discount_configuration';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['shop_discount_id', 'shop_discount_entity_id'], 'required'],
            [['shop_discount_id', 'shop_discount_entity_id'], 'integer'],
            [['shop_discount_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopDiscount::className(), 'targetAttribute' => ['shop_discount_id' => 'id']],
            [['shop_discount_entity_id'], 'exist', 'skipOnError' => true, 'targetClass' => SsShopDiscountEntity::className(), 'targetAttribute' => ['shop_discount_entity_id' => 'id']],
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
            'shop_discount_entity_id' => 'Shop Discount Entity ID',
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
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDiscountEntity()
    {
        return $this->hasOne($this->called_class_namespace . '\SsShopDiscountEntity', ['id' => 'shop_discount_entity_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSsShopDiscountValues()
    {
        return $this->hasMany($this->called_class_namespace . '\SsShopDiscountValues', ['shop_discount_configuration_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsShopDiscountConfigurationQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsShopDiscountConfigurationQuery(get_called_class());
    }
}
