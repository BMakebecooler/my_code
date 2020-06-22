<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "queue_log".
 *
 * @property integer $id ID
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $component Component
 * @property string $exchange_name Exchange Name
 * @property string $queue_name Queue Name
 * @property string $routing_key Routing Key
 * @property string $job_class Job Class
 * @property string $status Status
 * @property string $message Message
 * @property string $error Error
 * @property string $guid Guid
*/
class QueueLog extends \common\ActiveRecord
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
        return 'queue_log';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'integer'],
            [['message', 'error'], 'string'],
            [['component', 'exchange_name', 'queue_name', 'routing_key', 'job_class', 'guid'], 'string', 'max' => 64],
            [['status'], 'string', 'max' => 2],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'component' => 'Component',
            'exchange_name' => 'Exchange Name',
            'queue_name' => 'Queue Name',
            'routing_key' => 'Routing Key',
            'job_class' => 'Job Class',
            'status' => 'Status',
            'message' => 'Message',
            'error' => 'Error',
            'guid' => 'Guid',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\QueueLogQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\QueueLogQuery(get_called_class());
    }
}
