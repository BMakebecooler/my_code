<?php

/**
 * php ./yii task/task
 */

namespace console\controllers\task;
use common\helpers\Msg;
use modules\shopandshow\components\task\BaseTaskHandler;
use modules\shopandshow\components\task\MonitoringWeeklyTaskHandler;
use modules\shopandshow\models\task\SsTask;

/**
 * Class TaskController
 * @package console\controllers
 */
class TaskController extends \yii\console\Controller
{

    public function actionIndex()
    {
        $this->actionRunAllTasks();
    }

    /**
     * запуск всех новых заданий
     */
    public function actionRunAllTasks()
    {
        $tasks = SsTask::find()
            ->where([
                'status' => [
                    SsTask::STATUS_NEW,
                    //SsTask::STATUS_ERROR,
                ]
            ])
            ->all();

        foreach ($tasks as $task) {

            if(!$this->isAvailableToHandle($task)){
                $task->setSkipped();
                continue;
            }

            /** @var $task SsTask */
            $task->setInProgress();

            /** @var BaseTaskHandler $handler */
            $msg = '';
            $handler = $task->getHandler();
            if (!$handler) {
                $msg = 'Не удалось получить обработчика для задания '.$task->id;
                $this->report($msg);

                $task->setError();

                continue;
            }

            try {
                $result = $handler->handle();
            }
            catch (\Exception $e) {
                $result = false;
                $msg = 'Не удалось выполнить обработку задания ' . $task->id . ':' . PHP_EOL . $e->getMessage() . PHP_EOL . $e->getTraceAsString();
            }

            if (!$result) {
                $this->report('Не удалось обработать задание ' . $task->id . ': ' . ($msg ?: print_r($result, true)) );

                $task->setError();

                continue;
            }

            $task->setComplete();
        }
    }

    public function isAvailableToHandle(\modules\shopandshow\models\task\SsTask $task) {
        $taskDayGap = round((time() - $task->created_at) / 84600); //Задача просрочена на число дней
        $isAvailable = false;
        switch ($task->component) {
            case 'modules\shopandshow\components\task\SendOrderSmsTaskHandler':
                $taskDayGap <= 1 ? $isAvailable = true : $isAvailable = false; break;
            case 'modules\shopandshow\components\task\SendMailGunEmailTaskHandler':
                $taskDayGap <= 2 ? $isAvailable = true: $isAvailable = false; break;
            case 'modules\shopandshow\components\task\SendSurveyMailTaskHandler':
                $taskDayGap <= 2 ? $isAvailable = true: $isAvailable = false; break;
            case 'modules\shopandshow\components\task\MonitoringWeeklyTaskHandler':
                $taskDayGap <= 7 ? $isAvailable = true: $isAvailable = false; break;
            case 'modules\shopandshow\components\task\SendCoupons500rTaskHandler':
                //$taskDayGap <= 5 ? $isAvailable = true: $isAvailable = false; break;
                $isAvailable = true; break;
            case 'modules\shopandshow\components\task\SendRrEmailTaskHandler':
                $taskDayGap <= 2 ? $isAvailable = true: $isAvailable = false; break;
            break;
        }
        return  $isAvailable;
    }

    protected function report($msg)
    {
        if (YII_ENV == 'production') {
            \Yii::error($msg);
        }
        else {
            var_dump($msg);
        }
    }
}