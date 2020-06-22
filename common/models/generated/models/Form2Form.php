<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "form2_form".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $name Name
 * @property string $description Description
 * @property string $code Code
 * @property string $emails Emails
 * @property string $phones Phones
 * @property string $user_ids User Ids
 *
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
     * @property Form2FormProperty[] $form2FormProperties
     * @property Form2FormSend[] $form2FormSends
    */
class Form2Form extends \common\ActiveRecord
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
        return 'form2_form';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'required'],
            [['description', 'emails', 'phones', 'user_ids'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 32],
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
            'name' => 'Name',
            'description' => 'Description',
            'code' => 'Code',
            'emails' => 'Emails',
            'phones' => 'Phones',
            'user_ids' => 'User Ids',
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
    public function getForm2FormProperties()
    {
        return $this->hasMany($this->called_class_namespace . '\Form2FormProperty', ['form_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getForm2FormSends()
    {
        return $this->hasMany($this->called_class_namespace . '\Form2FormSend', ['form_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\Form2FormQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\Form2FormQuery(get_called_class());
    }
}
