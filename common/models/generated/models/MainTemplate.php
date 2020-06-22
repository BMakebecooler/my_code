<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "main_template".
 *
 * @property integer $id ID
 * @property integer $active Active
 * @property string $name Name
 * @property string $description Description
 * @property integer $start_timestamp Start Timestamp
 * @property integer $end_timestamp End Timestamp
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 *
     * @property MainPage[] $mainPages
     * @property MainTemplateBlock[] $mainTemplateBlocks
    */
class MainTemplate extends \common\ActiveRecord
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
        return 'main_template';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['active', 'start_timestamp', 'end_timestamp', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['name'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'active' => 'Active',
            'name' => 'Name',
            'description' => 'Description',
            'start_timestamp' => 'Start Timestamp',
            'end_timestamp' => 'End Timestamp',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getMainPages()
    {
        return $this->hasMany($this->called_class_namespace . '\MainPage', ['template_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getMainTemplateBlocks()
    {
        return $this->hasMany($this->called_class_namespace . '\MainTemplateBlock', ['template_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\MainTemplateQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\MainTemplateQuery(get_called_class());
    }
}
