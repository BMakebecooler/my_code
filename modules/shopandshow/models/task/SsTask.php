<?php

namespace modules\shopandshow\models\task;

use modules\shopandshow\components\task\BaseTaskHandler;
use skeeks\cms\models\behaviors\Serialize;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%ss_task}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 * @property string $component
 * @property string $component_settings
 *
 * @property BaseTaskHandler $handler
 */
class SsTask extends \skeeks\cms\models\Core
{
    const STATUS_NEW = 1;
    const STATUS_COMPLETE = 2;
    const STATUS_ERROR = 3;
    const STATUS_IN_PROGRESS = 4;
    const STATUS_SKIPPED = 5;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ss_task}}';
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            Serialize::className() =>
                [
                    'class' => Serialize::className(),
                    'fields' => ['component_settings']
                ]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'id'], 'integer'],
            [['component'], 'required'],
            [['component_settings'], 'safe'],
            [['status'], 'integer'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id' => 'ID',
            'status' => 'Status',
            'component' => 'Component',
            'component_settings' => 'Component Settings',
        ]);
    }

    /**
     * Создает новую задачу
     * @param $component
     * @param array $componentSettings
     * @return mixed
     */
    public static function createNewTask($component, $componentSettings = [])
    {
        $task = new static();
        $task->status = static::STATUS_NEW;
        $task->component = $component;
        $task->component_settings = $componentSettings;

        return $task->save();
    }

    /**
     * @return Component|boolean
     */
    public function getHandler()
    {
        if ($this->component) {
            try {
                /**
                 * @var $component BaseTaskHandler
                 */
                $component = \Yii::createObject($this->component);
                $component->taskModel = $this;
                \Yii::configure($component, $this->component_settings);

                return $component;
            } catch (\Exception $e) {
                return false;
            }

        }

        return null;
    }

    public function setComplete()
    {
        $this->status = self::STATUS_COMPLETE;
        return $this->save(['status']);
    }

    public function setError()
    {
        $this->status = self::STATUS_ERROR;
        return $this->save(['status']);
    }

    public function setInProgress()
    {
        $this->status = self::STATUS_IN_PROGRESS;
        return $this->save(['status']);
    }

    public function setSkipped()
    {
        $this->status = self::STATUS_SKIPPED;
        return $this->save(['status']);
    }
}