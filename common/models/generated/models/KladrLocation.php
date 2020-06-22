<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "kladr_location".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $name Name
 * @property string $name_short Name Short
 * @property string $name_full Name Full
 * @property string $zip Zip
 * @property string $okato Okato
 * @property string $type Type
 * @property string $kladr_api_id Kladr Api ID
 * @property string $active Active
 * @property integer $parent_id Parent ID
 * @property integer $sort Sort
 *
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
     * @property ShopStore[] $shopStores
    */
class KladrLocation extends \common\ActiveRecord
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
        return 'kladr_location';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'parent_id', 'sort'], 'integer'],
            [['name', 'type'], 'required'],
            [['name', 'name_short', 'name_full'], 'string', 'max' => 255],
            [['zip', 'okato', 'kladr_api_id'], 'string', 'max' => 20],
            [['type'], 'string', 'max' => 10],
            [['active'], 'string', 'max' => 1],
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
            'name' => 'Name',
            'name_short' => 'Name Short',
            'name_full' => 'Name Full',
            'zip' => 'Zip',
            'okato' => 'Okato',
            'type' => 'Type',
            'kladr_api_id' => 'Kladr Api ID',
            'active' => 'Active',
            'parent_id' => 'Parent ID',
            'sort' => 'Sort',
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
    public function getShopStores()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopStore', ['location_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\KladrLocationQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\KladrLocationQuery(get_called_class());
    }
}
