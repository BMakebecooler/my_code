<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_fuser_favorites".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $shop_fuser_id Shop Fuser ID
 * @property integer $shop_product_id Shop Product ID
 * @property string $active Active
 * @property string $comment Comment
 *
     * @property ShopFuser $shopFuser
     * @property ShopProduct $shopProduct
     * @property CmsContentElement $shopProduct0
    */
class ShopFuserFavorites extends \common\ActiveRecord
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
        return 'shop_fuser_favorites';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'shop_fuser_id', 'shop_product_id'], 'integer'],
            [['shop_fuser_id', 'shop_product_id'], 'required'],
            [['comment'], 'string'],
            [['active'], 'string', 'max' => 1],
            [['shop_fuser_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopFuser::className(), 'targetAttribute' => ['shop_fuser_id' => 'id']],
            [['shop_product_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopProduct::className(), 'targetAttribute' => ['shop_product_id' => 'id']],
            [['shop_product_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['shop_product_id' => 'id']],
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
            'shop_fuser_id' => 'Shop Fuser ID',
            'shop_product_id' => 'Shop Product ID',
            'active' => 'Active',
            'comment' => 'Comment',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopFuser()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopFuser', ['id' => 'shop_fuser_id']);
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
    public function getShopProduct0()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContentElement', ['id' => 'shop_product_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ShopFuserFavoritesQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopFuserFavoritesQuery(get_called_class());
    }
}
