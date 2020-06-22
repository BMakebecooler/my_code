<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "cms_user_universal_property_enum".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $property_id Property ID
 * @property string $value Value
 * @property string $def Def
 * @property string $code Code
 * @property integer $priority Priority
 *
     * @property CmsUser $createdBy
     * @property CmsUserUniversalProperty $property
     * @property CmsUser $updatedBy
    */
class CmsUserUniversalPropertyEnum extends \common\ActiveRecord
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
        return 'cms_user_universal_property_enum';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'property_id', 'priority'], 'integer'],
            [['value', 'code'], 'required'],
            [['value'], 'string', 'max' => 255],
            [['def'], 'string', 'max' => 1],
            [['code'], 'string', 'max' => 32],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['property_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUserUniversalProperty::className(), 'targetAttribute' => ['property_id' => 'id']],
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
            'value' => 'Value',
            'def' => 'Def',
            'code' => 'Code',
            'priority' => 'Priority',
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
    public function getProperty()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUserUniversalProperty', ['id' => 'property_id']);
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
     * @return \common\models\query\CmsUserUniversalPropertyEnumQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CmsUserUniversalPropertyEnumQuery(get_called_class());
    }
}
