<?php
namespace modules\shopandshow\components\task;

/**
 * Interface TaskHandlerInterface
 */
interface TaskHandlerInterface
{
    /**
     * @return boolean
     */
    public function handle();
}