<?php
namespace console\controllers\queues;

use console\controllers\queues\jobs\Job;
use modules\shopandshow\components\amqp\SSMessageBus;

class Listener
{

    public $exchangeName;
    public $queueName;
    public $routingKey;
    public $queue;
    public $jobClass;
    public $onlySave = false;
    public $daemonMode = true;
    public $vhost;
    public $nolog = false;

    /** @var Job */
    protected $handler;


    public function __construct()
    {
        \Yii::$app->db->close();
        \Yii::$app->db->open();
    }

    public function setHandler()
    {
        $this->handler = ($this->handler) ?: \Yii::createObject($this->jobClass);
    }

    public function listen()
    {
        /**
         * @var SSMessageBus $queue
         */
        $queue = \Yii::$app->{$this->queue};

        if (!empty($this->vhost)) {
            $queue->vhost = $this->vhost;
        }
        $queue->exchangeName = $this->exchangeName;
        $queue->queueName = $this->queueName;
        $queue->routingKey = $this->routingKey;
        $queue->messageHandler = function ($id, $message) use ($queue) {

            if (!$message) return true;

            if ($this->onlySave) {
                echo '.';
                return $this->logMessage($message, QueueLog::STATUS_DELAYED_PROCESS);
            }

            $guid = null;
            $attempt = 0;
            ob_start();
            // делаем 3 попытки на выполнение запроса, т.к. из очереди сообщения летят асинхронно, и могут быть некоторые траблы инконсистентности
            do {
                try {
                    // не первая попытка, ждем
                    if ($attempt > 0) {
                        sleep(1);
                    }
                    $guid = null;
                    $result = $this->handler->execute($message, $guid);
                } catch (\Throwable $e) {
                    $result = false;
                    echo $e->getTraceAsString();
                }
            } while ($result != true && ++$attempt < 3);

            if ($this->nolog == false) {
                $status = $result ? QueueLog::STATUS_COMPLETED : QueueLog::STATUS_ERROR;
                $this->logMessage($message, $status, ob_get_contents(), $guid);
            }
            ob_end_flush();

            return true;
        };

        // если запущен не как демон
        if (!$this->daemonMode) {
            $time = time();
            // если 30 сек не было сообщений
            $queue->waitTimeout = 30;
            $queue->stopHandler = function ($force = false) use ($queue, $time) {
                // или трудится больше 5 мин
                if ($force || time() - $time > MIN_10) {
                    echo '[!] stopping '.$queue->exchangeName.'...'.PHP_EOL;

                    return true;
                }
            };
        }

        $queue->listen();
    }

    /**
     * Логирование необработанных сообщений
     * @param string $message
     * @param string $status
     * @param string $error
     * @param string $guid
     *
     * @return bool
     */
    public function logMessage($message, $status = QueueLog::STATUS_ERROR, $error = '', $guid = '')
    {
        if (empty($message)) {
            return false;
        }

        $queueLog = new \console\controllers\queues\QueueLog([
            'component' => $this->queue,
            'exchange_name' => $this->exchangeName,
            'queue_name' => $this->queueName,
            'routing_key' => $this->routingKey,
            'job_class' => $this->jobClass,
            'status' => $status,
            'message' => $message,
            'error' => $error,
            'guid' => $guid
        ]);

        if (!$queueLog->save()) {
            var_dump($queueLog->getErrors());
            var_dump($message);

            return false;
        }

        return true;
    }
}
