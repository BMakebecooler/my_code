<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "scheduler_log".
 *
 * @property integer $id ID
 * @property integer $scheduler_task_id Scheduler Task ID
 * @property string $started_at Started At
 * @property string $ended_at Ended At
 * @property string $output Output
 * @property integer $error Error
 *
     * @property SchedulerTask $schedulerTask
    */
class SchedulerLog extends \common\ActiveRecord
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
        return 'scheduler_log';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['scheduler_task_id', 'output'], 'required'],
            [['scheduler_task_id', 'error'], 'integer'],
            [['started_at', 'ended_at'], 'safe'],
            [['output'], 'string'],
            [['scheduler_task_id'], 'exist', 'skipOnError' => true, 'targetClass' => SchedulerTask::className(), 'targetAttribute' => ['scheduler_task_id' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'scheduler_task_id' => 'Scheduler Task ID',
            'started_at' => 'Started At',
            'ended_at' => 'Ended At',
            'output' => 'Output',
            'error' => 'Error',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSchedulerTask()
    {
        return $this->hasOne($this->called_class_namespace . '\SchedulerTask', ['id' => 'scheduler_task_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SchedulerLogQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SchedulerLogQuery(get_called_class());
    }
}
