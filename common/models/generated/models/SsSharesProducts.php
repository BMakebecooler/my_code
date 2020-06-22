<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_shares_products".
 *
 * @property integer $banner_id Banner ID
 * @property integer $bitrix_id Bitrix ID
 * @property integer $product_id Product ID
 * @property integer $priority Priority
 * @property integer $is_hidden_catalog Is Hidden Catalog
 *
     * @property SsShares $banner
     * @property CmsContentElement $product
    */
class SsSharesProducts extends \common\ActiveRecord
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
        return 'ss_shares_products';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['banner_id', 'bitrix_id'], 'required'],
            [['banner_id', 'bitrix_id', 'product_id', 'priority', 'is_hidden_catalog'], 'integer'],
            [['banner_id', 'bitrix_id'], 'unique', 'targetAttribute' => ['banner_id', 'bitrix_id'], 'message' => 'The combination of Banner ID and Bitrix ID has already been taken.'],
            [['banner_id'], 'exist', 'skipOnError' => true, 'targetClass' => SsShares::className(), 'targetAttribute' => ['banner_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'banner_id' => 'Banner ID',
            'bitrix_id' => 'Bitrix ID',
            'product_id' => 'Product ID',
            'priority' => 'Priority',
            'is_hidden_catalog' => 'Is Hidden Catalog',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getBanner()
    {
        return $this->hasOne($this->called_class_namespace . '\SsShares', ['id' => 'banner_id']);
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
     * @return \common\models\query\SsSharesProductsQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsSharesProductsQuery(get_called_class());
    }
}
