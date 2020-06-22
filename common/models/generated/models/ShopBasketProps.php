<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_basket_props".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $shop_basket_id Shop Basket ID
 * @property string $name Name
 * @property string $value Value
 * @property string $code Code
 * @property integer $priority Priority
 *
     * @property ShopBasket $shopBasket
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
    */
class ShopBasketProps extends \common\ActiveRecord
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
        return 'shop_basket_props';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'shop_basket_id', 'priority'], 'integer'],
            [['shop_basket_id', 'name'], 'required'],
            [['name', 'value', 'code'], 'string', 'max' => 255],
            [['shop_basket_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopBasket::className(), 'targetAttribute' => ['shop_basket_id' => 'id']],
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
            'shop_basket_id' => 'Shop Basket ID',
            'name' => 'Name',
            'value' => 'Value',
            'code' => 'Code',
            'priority' => 'Priority',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopBasket()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopBasket', ['id' => 'shop_basket_id']);
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
     * @return \common\models\query\ShopBasketPropsQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopBasketPropsQuery(get_called_class());
    }
}
