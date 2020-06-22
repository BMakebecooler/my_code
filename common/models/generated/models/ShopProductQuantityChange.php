<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_product_quantity_change".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $shop_product_id Shop Product ID
 * @property double $quantity Quantity
 * @property double $quantity_reserved Quantity Reserved
 * @property integer $measure_id Measure ID
 * @property double $measure_ratio Measure Ratio
 *
     * @property CmsUser $createdBy
     * @property Measure $measure
     * @property ShopProduct $shopProduct
     * @property CmsUser $updatedBy
    */
class ShopProductQuantityChange extends \common\ActiveRecord
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
    public function behaviors()
    {
        return [
            'author' => \yii\behaviors\BlameableBehavior::class,
            'timestamp' => \yii\behaviors\TimestampBehavior::class,
        ];
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'shop_product_quantity_change';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'shop_product_id', 'measure_id'], 'integer'],
            [['shop_product_id'], 'required'],
            [['quantity', 'quantity_reserved', 'measure_ratio'], 'number'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['measure_id'], 'exist', 'skipOnError' => true, 'targetClass' => Measure::className(), 'targetAttribute' => ['measure_id' => 'id']],
            [['shop_product_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopProduct::className(), 'targetAttribute' => ['shop_product_id' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'shop_product_id' => 'Shop Product ID',
            'quantity' => 'Quantity',
            'quantity_reserved' => 'Quantity Reserved',
            'measure_id' => 'Measure ID',
            'measure_ratio' => 'Measure Ratio',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCreatedBy()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'created_by']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getMeasure()
    {
        return $this->hasOne($this->called_class_namespace . '\Measure', ['id' => 'measure_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopProduct()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopProduct', ['id' => 'shop_product_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getUpdatedBy()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'updated_by']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ShopProductQuantityChangeQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopProductQuantityChangeQuery(get_called_class());
    }
}
