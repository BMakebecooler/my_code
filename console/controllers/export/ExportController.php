<?php

/**
 * php ./yii export/export/run-all [1,2,3] - ИД задач на экспорт, через ЗПТ
 */


namespace console\controllers\export;


use console\jobs\ExportJob;
use skeeks\cms\export\models\ExportTask;
use Yii;
use yii\helpers\Console;


/**
 * Class ExportController
 *
 * @package console\controllers
 */
class ExportController extends \yii\console\Controller
{
    public $id;

    protected $agentStartTime;

    protected $logger;

    protected $shopAndShow;


    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'id',
        ]);
    }

    public function beforeAction($action)
    {

        $this->stdout("\nBegin: " . $action->getUniqueId() . "\n\n", Console::FG_YELLOW);

        $this->agentStartTime = time();

        return true;
    }

    public function afterAction($action, $result)
    {

        $this->stdout("\n\nElapsed: " . (time() - $this->agentStartTime) . "sec.\n", Console::FG_YELLOW);

        return parent::afterAction($action, $result);
    }


    public function delay(int $sec)
    {
        $this->stdout("Sleeping for {$sec}sec. ", Console::FG_GREY);
        for ($k = 1; $k <= $sec; $k++) {
            $this->stdout("{$k}... ");
            sleep(1);
        }
        $this->stdout("\n");
    }

    /**Запускает обработку заданий на экспорт
     *
     * @param null $ids - список id заданий на экпорт которые необходимо выполнить (через ЗПТ)
     */
    public function actionRunAll($ids = null)
    {
        $exportTasks = ExportTask::find();

        if ($ids) {
            $exportTasks->where(['id' => explode(',', $ids)]);
        }

        $exportTasks = $exportTasks->all();

        $this->stdout("Найдено заданий для экспорта " . count($exportTasks) . PHP_EOL, Console::FG_GREEN);

        foreach ($exportTasks as $exportTask) {
            $r = Yii::$app->queueFeed->push(new ExportJob([
                'id' => $exportTask->id,
            ]));
            $this->stdout('Add to queue ' . $r . PHP_EOL, Console::FG_GREEN);
        }

    }

    public function actionRunOne()
    {
        $id = $this->id;
        $this->stdout('Export one ' . $id . PHP_EOL, Console::FG_GREEN);
        $exportTask = ExportTask::findOne($id);

        $handler = $exportTask->handler;
        if ($handler) {
            $this->stdout("Start  " . $exportTask->name . ', Component ' . $exportTask->component . PHP_EOL, Console::FG_GREY);

            try {
                $result = $handler->export();
            } catch (\Exception $e) {
                $this->stdout($e->getMessage() . PHP_EOL);
                $this->stdout($e->getTraceAsString() . PHP_EOL);

                \Yii::error('Failed execute task ' . $exportTask->name . ', error ' . $e->getMessage());
            }
        }
    }

}