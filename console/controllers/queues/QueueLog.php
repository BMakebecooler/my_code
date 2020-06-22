<?php
namespace console\controllers\queues;

use common\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;


/**
 * Class QueueLog
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string $component
 * @property string $exchange_name
 * @property string $queue_name
 * @property string $routing_key
 * @property string $job_class
 * @property string $status
 * @property string $message
 * @property string $error
 * @property string $guid
 *
 * @package console\controllers\queues
 */
class QueueLog extends \yii\db\ActiveRecord
{

    const STATUS_ERROR = 'E';
    const STATUS_COMPLETED = 'C';
    const STATUS_DELAYED_PROCESS = 'DP';
    const STATUS_PUSHED = 'P';

    public static function tableName()
    {
        return 'queue_log';
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            TimestampBehavior::className() =>
                [
                    'class' => TimestampBehavior::className(),
                    /*'value' => function()
                    {
                        return date('U');
                    },*/
                ],
        ]);
    }

    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at'], 'integer'],
            [['component', 'exchange_name', 'queue_name', 'routing_key', 'job_class', 'status', 'message', 'error', 'guid'], 'string'],
        ];
    }
}