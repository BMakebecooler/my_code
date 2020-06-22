<?php
namespace modules\shopandshow\components\task;

use modules\shopandshow\models\task\SsTask;
use yii\base\Component;

/**
 * Class BaseTaskHandler
 */
abstract class BaseTaskHandler extends Component implements TaskHandlerInterface
{
    /**
     * @var SsTask
     */
    public $taskModel;

    /**
     * @return bool
     */
    abstract function handle();
}