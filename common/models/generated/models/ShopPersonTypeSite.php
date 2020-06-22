<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_person_type_site".
 *
 * @property integer $person_type_id Person Type ID
 * @property string $site_code Site Code
 *
     * @property ShopPersonType $personType
     * @property CmsSite $siteCode
    */
class ShopPersonTypeSite extends \common\ActiveRecord
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
        return 'shop_person_type_site';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['person_type_id', 'site_code'], 'required'],
            [['person_type_id'], 'integer'],
            [['site_code'], 'string', 'max' => 15],
            [['person_type_id', 'site_code'], 'unique', 'targetAttribute' => ['person_type_id', 'site_code'], 'message' => 'The combination of Person Type ID and Site Code has already been taken.'],
            [['person_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopPersonType::className(), 'targetAttribute' => ['person_type_id' => 'id']],
            [['site_code'], 'exist', 'skipOnError' => true, 'targetClass' => CmsSite::className(), 'targetAttribute' => ['site_code' => 'code']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'person_type_id' => 'Person Type ID',
            'site_code' => 'Site Code',
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
    public function getSiteCode()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsSite', ['code' => 'site_code']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ShopPersonTypeSiteQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopPersonTypeSiteQuery(get_called_class());
    }
}
