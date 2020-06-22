<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_tax_rate".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $tax_id Tax ID
 * @property integer $person_type_id Person Type ID
 * @property string $value Value
 * @property string $currency Currency
 * @property string $is_percent Is Percent
 * @property string $is_in_price Is In Price
 * @property integer $priority Priority
 * @property string $active Active
 *
     * @property CmsUser $createdBy
     * @property ShopPersonType $personType
     * @property ShopTax $tax
     * @property CmsUser $updatedBy
    */
class ShopTaxRate extends \common\ActiveRecord
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
        return 'shop_tax_rate';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'tax_id', 'person_type_id', 'priority'], 'integer'],
            [['tax_id', 'person_type_id'], 'required'],
            [['value'], 'number'],
            [['currency'], 'string', 'max' => 3],
            [['is_percent', 'is_in_price', 'active'], 'string', 'max' => 1],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['person_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopPersonType::className(), 'targetAttribute' => ['person_type_id' => 'id']],
            [['tax_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopTax::className(), 'targetAttribute' => ['tax_id' => 'id']],
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
            'tax_id' => 'Tax ID',
            'person_type_id' => 'Person Type ID',
            'value' => 'Value',
            'currency' => 'Currency',
            'is_percent' => 'Is Percent',
            'is_in_price' => 'Is In Price',
            'priority' => 'Priority',
            'active' => 'Active',
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
    public function getPersonType()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopPersonType', ['id' => 'person_type_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getTax()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopTax', ['id' => 'tax_id']);
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
     * @return \common\models\query\ShopTaxRateQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopTaxRateQuery(get_called_class());
    }
}
