<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "measure".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $code Code
 * @property string $name Name
 * @property string $symbol_rus Symbol Rus
 * @property string $symbol_intl Symbol Intl
 * @property string $symbol_letter_intl Symbol Letter Intl
 * @property string $def Def
 *
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
     * @property ShopProduct[] $shopProducts
     * @property ShopProductQuantityChange[] $shopProductQuantityChanges
    */
class Measure extends \common\ActiveRecord
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
        return 'measure';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'code'], 'integer'],
            [['code'], 'required'],
            [['name'], 'string', 'max' => 500],
            [['symbol_rus', 'symbol_intl', 'symbol_letter_intl'], 'string', 'max' => 20],
            [['def'], 'string', 'max' => 1],
            [['code'], 'unique'],
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
            'code' => 'Code',
            'name' => 'Name',
            'symbol_rus' => 'Symbol Rus',
            'symbol_intl' => 'Symbol Intl',
            'symbol_letter_intl' => 'Symbol Letter Intl',
            'def' => 'Def',
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
    public function getUpdatedBy()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'updated_by']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopProducts()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopProduct', ['measure_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopProductQuantityChanges()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopProductQuantityChange', ['measure_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\MeasureQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\MeasureQuery(get_called_class());
    }
}
