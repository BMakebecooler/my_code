<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_mail_dispatch".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $mail_template_id Mail Template ID
 * @property string $status Status
 * @property string $from From
 * @property string $to To
 * @property string $segments Segments
 * @property string $subject Subject
 * @property string $body Body
 * @property string $message Message
 *
     * @property SsMailTemplate $mailTemplate
    */
class SsMailDispatch extends \common\ActiveRecord
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
        return 'ss_mail_dispatch';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'mail_template_id'], 'integer'],
            [['status', 'from', 'subject'], 'required'],
            [['body', 'message'], 'string'],
            [['status'], 'string', 'max' => 32],
            [['from'], 'string', 'max' => 255],
            [['to'], 'string', 'max' => 1024],
            [['segments', 'subject'], 'string', 'max' => 512],
            [['mail_template_id'], 'exist', 'skipOnError' => true, 'targetClass' => SsMailTemplate::className(), 'targetAttribute' => ['mail_template_id' => 'id']],
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
            'mail_template_id' => 'Mail Template ID',
            'status' => 'Status',
            'from' => 'From',
            'to' => 'To',
            'segments' => 'Segments',
            'subject' => 'Subject',
            'body' => 'Body',
            'message' => 'Message',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getMailTemplate()
    {
        return $this->hasOne($this->called_class_namespace . '\SsMailTemplate', ['id' => 'mail_template_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsMailDispatchQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsMailDispatchQuery(get_called_class());
    }
}
