<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_mail_subject".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $name Name
 * @property string $active Active
 * @property integer $begin_datetime Begin Datetime
 * @property integer $end_datetime End Datetime
 * @property string $subject Subject
 * @property integer $template_id Template ID
 *
     * @property SsMailTemplate $template
    */
class SsMailSubject extends \common\ActiveRecord
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
        return 'ss_mail_subject';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'begin_datetime', 'end_datetime', 'template_id'], 'integer'],
            [['name', 'active', 'subject'], 'required'],
            [['name', 'subject'], 'string', 'max' => 255],
            [['active'], 'string', 'max' => 1],
            [['template_id'], 'exist', 'skipOnError' => true, 'targetClass' => SsMailTemplate::className(), 'targetAttribute' => ['template_id' => 'id']],
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
            'active' => 'Active',
            'begin_datetime' => 'Begin Datetime',
            'end_datetime' => 'End Datetime',
            'subject' => 'Subject',
            'template_id' => 'Template ID',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getTemplate()
    {
        return $this->hasOne($this->called_class_namespace . '\SsMailTemplate', ['id' => 'template_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsMailSubjectQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsMailSubjectQuery(get_called_class());
    }
}
