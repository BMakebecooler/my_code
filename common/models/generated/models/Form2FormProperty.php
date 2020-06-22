<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "form2_form_property".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $name Name
 * @property string $code Code
 * @property string $active Active
 * @property integer $priority Priority
 * @property string $property_type Property Type
 * @property string $multiple Multiple
 * @property string $is_required Is Required
 * @property string $component Component
 * @property string $component_settings Component Settings
 * @property string $hint Hint
 * @property integer $form_id Form ID
 *
     * @property CmsUser $createdBy
     * @property Form2Form $form
     * @property CmsUser $updatedBy
     * @property Form2FormPropertyEnum[] $form2FormPropertyEnums
     * @property Form2FormSendProperty[] $form2FormSendProperties
    */
class Form2FormProperty extends \common\ActiveRecord
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
        return 'form2_form_property';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'priority', 'form_id'], 'integer'],
            [['name'], 'required'],
            [['component_settings'], 'string'],
            [['name', 'component', 'hint'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 64],
            [['active', 'property_type', 'multiple', 'is_required'], 'string', 'max' => 1],
            [['code', 'form_id'], 'unique', 'targetAttribute' => ['code', 'form_id'], 'message' => 'The combination of Code and Form ID has already been taken.'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['form_id'], 'exist', 'skipOnError' => true, 'targetClass' => Form2Form::className(), 'targetAttribute' => ['form_id' => 'id']],
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
            'code' => 'Code',
            'active' => 'Active',
            'priority' => 'Priority',
            'property_type' => 'Property Type',
            'multiple' => 'Multiple',
            'is_required' => 'Is Required',
            'component' => 'Component',
            'component_settings' => 'Component Settings',
            'hint' => 'Hint',
            'form_id' => 'Form ID',
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
    public function getForm()
    {
        return $this->hasOne($this->called_class_namespace . '\Form2Form', ['id' => 'form_id']);
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
    public function getForm2FormPropertyEnums()
    {
        return $this->hasMany($this->called_class_namespace . '\Form2FormPropertyEnum', ['property_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getForm2FormSendProperties()
    {
        return $this->hasMany($this->called_class_namespace . '\Form2FormSendProperty', ['property_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\Form2FormPropertyQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\Form2FormPropertyQuery(get_called_class());
    }
}
