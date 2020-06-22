<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "queue".
 *
 * @property integer $id ID
 * @property string $channel Channel
 * @property resource $job Job
 * @property integer $created_at Created At
 * @property integer $timeout Timeout
 * @property integer $started_at Started At
 * @property integer $finished_at Finished At
*/
class Queue extends \common\ActiveRecord
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
        ];
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'queue';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['channel', 'job', 'created_at'], 'required'],
            [['job'], 'string'],
            [['created_at', 'timeout', 'started_at', 'finished_at'], 'integer'],
            [['channel'], 'string', 'max' => 255],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'channel' => 'Channel',
            'job' => 'Job',
            'created_at' => 'Created At',
            'timeout' => 'Timeout',
            'started_at' => 'Started At',
            'finished_at' => 'Finished At',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\QueueQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\QueueQuery(get_called_class());
    }
}
