<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "product_param".
 *
 * @property integer $id ID
 * @property string $name Name
 * @property integer $type_id Type ID
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $count_can_sale Count Can Sale
 *
     * @property ProductParamType $type
     * @property ProductParamProduct[] $productParamProducts
     * @property CmsContentElement[] $products
     * @property SizeProfileParams[] $sizeProfileParams
    */
class ProductParam extends \common\ActiveRecord
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
            'timestamp' => \yii\behaviors\TimestampBehavior::class,
            'author' => \yii\behaviors\BlameableBehavior::class,
        ];
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'product_param';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['name', 'type_id'], 'required'],
            [['type_id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'count_can_sale'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductParamType::className(), 'targetAttribute' => ['type_id' => 'id']],
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
            'type_id' => 'Type ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'count_can_sale' => 'Count Can Sale',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getType()
    {
        return $this->hasOne($this->called_class_namespace . '\ProductParamType', ['id' => 'type_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getProductParamProducts()
    {
        return $this->hasMany($this->called_class_namespace . '\ProductParamProduct', ['product_param_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getProducts()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElement', ['id' => 'product_id'])->viaTable('product_param_product', ['product_param_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSizeProfileParams()
    {
        return $this->hasMany($this->called_class_namespace . '\SizeProfileParams', ['param_id' => 'id']);
    }
    /**
     * @inheritdoc
     * @return \common\models\query\ProductParamQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ProductParamQuery(get_called_class());
    }
}
