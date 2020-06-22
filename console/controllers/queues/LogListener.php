<?php
namespace console\controllers\queues;

class LogListener extends Listener
{

    public $jobMessages = [];

    public function listen()
    {
        var_dump('working for '.$this->exchangeName);
        var_dump('got messages '.sizeof($this->jobMessages));

        foreach ($this->jobMessages as $job) {
            ob_start();

            $guid = null;
            $result = $this->handler->execute($job['message'], $guid);
            $status = $result ? QueueLog::STATUS_COMPLETED : QueueLog::STATUS_ERROR;

            $queueLog = QueueLog::findOne($job['id']);
            $queueLog->status = $status;
            $queueLog->error = ob_get_contents();
            $queueLog->guid = $guid;
            $queueLog->save();

            ob_end_flush();
        }
    }
}
