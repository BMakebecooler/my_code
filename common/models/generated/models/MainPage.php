<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "main_page".
 *
 * @property integer $id ID
 * @property integer $active Active
 * @property string $name Name
 * @property string $code Code
 * @property integer $template_id Template ID
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 *
     * @property MainTemplate $template
    */
class MainPage extends \common\ActiveRecord
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
        return 'main_page';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['active', 'template_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['name', 'code', 'template_id'], 'required'],
            [['name', 'code'], 'string', 'max' => 255],
            [['template_id'], 'exist', 'skipOnError' => true, 'targetClass' => MainTemplate::className(), 'targetAttribute' => ['template_id' => 'id']],
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
            'code' => 'Code',
            'template_id' => 'Template ID',
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
    public function getTemplate()
    {
        return $this->hasOne($this->called_class_namespace . '\MainTemplate', ['id' => 'template_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\MainPageQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\MainPageQuery(get_called_class());
    }
}
