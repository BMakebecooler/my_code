<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "scheduler_task".
 *
 * @property integer $id ID
 * @property string $name Name
 * @property string $schedule Schedule
 * @property string $description Description
 * @property integer $status_id Status ID
 * @property string $started_at Started At
 * @property string $last_run Last Run
 * @property string $next_run Next Run
 * @property integer $active Active
 *
     * @property SchedulerLog[] $schedulerLogs
    */
class SchedulerTask extends \common\ActiveRecord
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
        return 'scheduler_task';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['name', 'schedule', 'description', 'status_id'], 'required'],
            [['description'], 'string'],
            [['status_id', 'active'], 'integer'],
            [['started_at', 'last_run', 'next_run'], 'safe'],
            [['name', 'schedule'], 'string', 'max' => 255],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'schedule' => 'Schedule',
            'description' => 'Description',
            'status_id' => 'Status ID',
            'started_at' => 'Started At',
            'last_run' => 'Last Run',
            'next_run' => 'Next Run',
            'active' => 'Active',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSchedulerLogs()
    {
        return $this->hasMany($this->called_class_namespace . '\SchedulerLog', ['scheduler_task_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SchedulerTaskQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SchedulerTaskQuery(get_called_class());
    }
}
