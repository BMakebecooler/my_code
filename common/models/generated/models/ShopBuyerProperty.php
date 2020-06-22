<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_buyer_property".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $property_id Property ID
 * @property integer $element_id Element ID
 * @property string $value Value
 * @property integer $value_enum Value Enum
 * @property string $value_num Value Num
 * @property string $description Description
 * @property integer $value_bool Value Bool
 *
     * @property CmsUser $createdBy
     * @property ShopBuyer $element
     * @property ShopPersonTypeProperty $property
     * @property CmsUser $updatedBy
    */
class ShopBuyerProperty extends \common\ActiveRecord
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
        return 'shop_buyer_property';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'property_id', 'element_id', 'value_enum', 'value_bool'], 'integer'],
            [['value'], 'required'],
            [['value_num'], 'number'],
            [['value', 'description'], 'string', 'max' => 255],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['element_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopBuyer::className(), 'targetAttribute' => ['element_id' => 'id']],
            [['property_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopPersonTypeProperty::className(), 'targetAttribute' => ['property_id' => 'id']],
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
            'property_id' => 'Property ID',
            'element_id' => 'Element ID',
            'value' => 'Value',
            'value_enum' => 'Value Enum',
            'value_num' => 'Value Num',
            'description' => 'Description',
            'value_bool' => 'Value Bool',
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
    public function getElement()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopBuyer', ['id' => 'element_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getProperty()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopPersonTypeProperty', ['id' => 'property_id']);
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
     * @return \common\models\query\ShopBuyerPropertyQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopBuyerPropertyQuery(get_called_class());
    }
}
