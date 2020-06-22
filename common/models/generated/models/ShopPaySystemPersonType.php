<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_pay_system_person_type".
 *
 * @property integer $pay_system_id Pay System ID
 * @property integer $person_type_id Person Type ID
 *
     * @property ShopPersonType $personType
     * @property ShopPaySystem $paySystem
    */
class ShopPaySystemPersonType extends \common\ActiveRecord
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
        return 'shop_pay_system_person_type';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['pay_system_id', 'person_type_id'], 'required'],
            [['pay_system_id', 'person_type_id'], 'integer'],
            [['pay_system_id', 'person_type_id'], 'unique', 'targetAttribute' => ['pay_system_id', 'person_type_id'], 'message' => 'The combination of Pay System ID and Person Type ID has already been taken.'],
            [['person_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopPersonType::className(), 'targetAttribute' => ['person_type_id' => 'id']],
            [['pay_system_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopPaySystem::className(), 'targetAttribute' => ['pay_system_id' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'pay_system_id' => 'Pay System ID',
            'person_type_id' => 'Person Type ID',
            ];
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
    public function getPaySystem()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopPaySystem', ['id' => 'pay_system_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ShopPaySystemPersonTypeQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopPaySystemPersonTypeQuery(get_called_class());
    }
}
