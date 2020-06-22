<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_discount2type_price".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $discount_id Discount ID
 * @property integer $type_price_id Type Price ID
 *
     * @property ShopDiscount $discount
     * @property ShopTypePrice $typePrice
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
    */
class ShopDiscount2typePrice extends \common\ActiveRecord
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
        return 'shop_discount2type_price';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'discount_id', 'type_price_id'], 'integer'],
            [['discount_id', 'type_price_id'], 'required'],
            [['discount_id', 'type_price_id'], 'unique', 'targetAttribute' => ['discount_id', 'type_price_id'], 'message' => 'The combination of Discount ID and Type Price ID has already been taken.'],
            [['discount_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopDiscount::className(), 'targetAttribute' => ['discount_id' => 'id']],
            [['type_price_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopTypePrice::className(), 'targetAttribute' => ['type_price_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
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
            'discount_id' => 'Discount ID',
            'type_price_id' => 'Type Price ID',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getDiscount()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopDiscount', ['id' => 'discount_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getTypePrice()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopTypePrice', ['id' => 'type_price_id']);
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
    public function getUpdatedBy()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'updated_by']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ShopDiscount2typePriceQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopDiscount2typePriceQuery(get_called_class());
    }
}
