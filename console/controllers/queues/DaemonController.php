<?php

/**
 * php ./yii queues/daemon/start
 * php ./yii queues/daemon/stop
 * php ./yii queues/daemon/status
 * php ./yii queues/daemon/start-log  //запуск обработки отложенных сообщений и сообщений с ошибками
 */

namespace console\controllers\queues;


class DaemonController extends \yii\console\Controller
{

    public $lockfile = '@runtime/queue_daemon_pid.lock';

    protected $lockfileHandler;
    protected $child_pids = [];
    protected $continue = true;

    protected $logDir = "@runtime/daemons/logs";


    public function init()
    {
        parent::init();
    }


    /**
     * @return bool
     */
    protected function lock()
    {
        if (!($this->lockfileHandler = fopen(\Yii::getAlias($this->lockfile), 'w+'))) {
            echo "unable to open\n";
            return false;
        }
        if (!flock($this->lockfileHandler, LOCK_EX | LOCK_NB)) {
            echo "unable to lock\n";
            return false;
        }
        @chmod(\Yii::getAlias($this->lockfile), 0775);
        @chown(\Yii::getAlias($this->lockfile), 'www-data');
        @chgrp(\Yii::getAlias($this->lockfile), 'www-data');

        return true;
    }

    protected function unlock()
    {
        if (!$this->lockfileHandler) return;
        flock($this->lockfileHandler, LOCK_UN);
        fclose($this->lockfileHandler);
        unlink(\Yii::getAlias($this->lockfile));
    }

    /**
     * @return bool|int
     */
    protected function status()
    {
        if (!($fp = fopen(\Yii::getAlias($this->lockfile), 'a+'))) {
            echo "Unable to open\n";
            return false;
        }

        if (flock($fp, LOCK_EX | LOCK_NB)) {
            flock($fp, LOCK_UN);
            fclose($fp);
            return false;
        }

        $pid = (int)fgets($fp);
        fclose($fp);
        return $pid;
    }

    public function actionStatus()
    {
        if ($pid = $this->status()) {
            echo "Демон запущен, PID=$pid\n";
        }
    }

    /**
     * @param int $pid
     * @param int $signal
     * @param string $signame
     */
    public function actionStop($pid = null, $signal = SIGKILL, $signame = 'TERM')
    {
        if (is_null($pid)) {
            $pid = $this->status();
        }
        if (!$pid) {
            echo "Процесс не работает\n";
            return;
        }

        foreach ($this->child_pids as $childPid) {
            echo "Sending $signame signal to child process $childPid\n";
            posix_kill($childPid, $signal);
        }

        echo "Sending $signame signal to parent process $pid\n";
        posix_kill($pid, $signal);

        while (posix_kill($pid, 0)) {
            usleep(100000);
        }
    }

    /**
     * @param int $pid
     */
    public function actionForceStop($pid = null)
    {
        $this->actionStop($pid, SIGKILL, 'KILL');
    }

    /**
     * @param bool $console
     */
    public function actionRestart($console = false)
    {
        if ($this->status()) {
            $this->actionStop();
        }
        $this->actionStart($console);
    }

    /**
     * @param bool $console
     */
    public function actionForceRestart($console = false)
    {
        if ($this->status()) {
            $this->actionForceStop();
        }
        $this->actionStart($console);
    }

    /**
     * signal handler
     */
    public function signalHandler($signo, $pid = null, $status = null)
    {
        switch ($signo) {
            case SIGTERM:
                echo "Received TERM, wrapping up\n";
                $this->continue = false;
                break;
        }
    }

    /**
     * @param bool $console whether to run in terminal instead of forking
     */
    public function actionStart($console = false)
    {

        if ($this->status()) {
            echo "Демон уже запущен\n";
            return;
        }

        echo 'locking PID... ';
        if (!$this->lock()) {
            echo "Неудача\n";
            return;
        }

        $pid = getmypid();
        fputs($this->lockfileHandler, $pid);
        if ($console) {
            echo "PID $pid\n";
        } else {
            posix_setsid();
        }

        declare(ticks = 1);
        pcntl_signal(SIGTERM, array($this, "signalHandler"));

        $queues = \Yii::$app->queueDaemons->getQueues();

        echo "Found ".sizeof($queues)." queues\n";
        echo "Starting...\n";

        foreach ($queues as $queue => $channel) {

            $pid = pcntl_fork();

            if ($pid == -1) {
                exit("Error forking...\n");
            } else if ($pid) {
                //echo "Forked child pid: $pid \n";
                $this->child_pids[] = $pid;
            } else if ($pid == 0) {

                echo 'Listening '.$channel['queueName'].(isset($channel['onlySave']) && $channel['onlySave'] ? ' [onlySave]' : '').PHP_EOL;

                @cli_set_process_title('queues/daemon/start '.$channel['exchangeName']);

                $listener = new Listener();
                $listener->queue = $channel['queue'];
                $listener->exchangeName = $channel['exchangeName'];
                $listener->queueName = $channel['queueName'];
                $listener->jobClass = $channel['jobClass'];
                $listener->routingKey = $channel['routingKey'];
                $listener->daemonMode = !$console;
                $listener->nolog = $channel['nolog'] ?? false;

                if (isset($channel['vhost'])) {
                    $listener->vhost = $channel['vhost'];
                }

                if (isset($channel['onlySave'])) {
                    $listener->onlySave = $channel['onlySave'];
                }

                $listener->setHandler();

                $listener->listen();

                exit();
            }

//            }

        }

        // This while loop holds the parent process until all the child threads
// are complete - at which point the script continues to execute.
        while (pcntl_waitpid(0, $status) != -1) ;

        echo 'unlocking... ' . PHP_EOL;
        $this->unlock();
    }

    /**
     * @param bool $console whether to run in terminal instead of forking
     * @param string $concreteQueue - queue name to resync
     */
    public function actionStartLog($console = true, $concreteQueue = null)
    {
        echo "starting...\n";

        $pid = getmypid();
        if ($console) {
            echo "PID $pid\n";
        } else {
            posix_setsid();
        }

        declare(ticks = 1);
        pcntl_signal(SIGTERM, array($this, "signalHandler"));

        $queues = \Yii::$app->queueDaemons->getQueues();

        // для каждой очереди свой набор сообщений
        foreach ($queues as $queue => $channel) {

            if( $concreteQueue && $concreteQueue != $queue) {
                continue;
            }

            \Yii::$app->db->close();
            \Yii::$app->db->open();

            $messages = QueueLog::find()
                //->andWhere(['component' => $channel['queue']])
                ->andWhere(['exchange_name' => $channel['exchangeName']])
                //->andWhere(['queue_name' => $channel['queueName']])
                //->andWhere(['job_class' => $channel['jobClass']])
                //->andWhere(['status' => [QueueLog::STATUS_DELAYED_PROCESS, QueueLog::STATUS_ERROR]])
                ->andWhere('(status = :status_delayed OR status = :status_error AND created_at > UNIX_TIMESTAMP(NOW() - INTERVAL 1 DAY))', [
                    ':status_delayed' => QueueLog::STATUS_DELAYED_PROCESS,
                    ':status_error' => QueueLog::STATUS_ERROR,
                ])
                ->limit(5000) //для теста
                ->asArray()->all();

            if (!$messages) continue;

            $workersCount = floor(log10(count($messages))) ? : 1;
            // делим задания поровну между потоками
            $jobMessagesChunks = array_chunk($messages, ceil(count($messages) / $workersCount));
            foreach ($jobMessagesChunks as $jobMessages) {
                $pid = pcntl_fork();

                if ($pid == -1) {
                    exit("Error forking...\n");
                } else {
                    if ($pid == 0) {

                        $listener = new LogListener();
                        $listener->queue = $channel['queue'];
                        $listener->exchangeName = $channel['exchangeName'];
                        $listener->queueName = $channel['queueName'];
                        $listener->jobClass = $channel['jobClass'];
                        $listener->jobMessages = $jobMessages;

                        $listener->setHandler();

                        $listener->listen();

                        exit();
                    }
                }
            }
        }


        // This while loop holds the parent process until all the child threads
// are complete - at which point the script continues to execute.
        while (pcntl_waitpid(0, $status) != -1) ;
    }

}