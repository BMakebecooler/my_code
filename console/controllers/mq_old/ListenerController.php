<?php

namespace console\controllers\mq;

use common\helpers\Msg;
use modules\shopandshow\components\amqp\SSMessageBus;
use yii\base\Exception;
use yii\helpers\Inflector;
use yii\helpers\Console;

/**
 * Class ListenerController
 *
 * @package console\controllers
 */
class ListenerController extends \yii\console\Controller
{

    public $queueName;
    public $routingKey;

    protected $logFile;
    protected $agentStartTime;

    protected
        $method,
        $data,
        $timestamp;

    public function beforeAction($action)
    {
        $this->log("Starting listener: " . $action->getUniqueId());
        $this->agentStartTime = time();

        if ($this->queueName === null)
            throw new Exception("Queue name is NULL");

        return true;
    }

    public function afterAction($action, $result)
    {
        $this->log("Listener ended. Duration: " . (time() - $this->agentStartTime) . "sec.\n");

        return parent::afterAction($action, $result);
    }

    public function parseMessage($message)
    {

        $_ = json_decode($message);

        if ($_->data === null || empty($_->data))
            throw new Exception("Data is null or empty");

        if (!method_exists($this, Inflector::id2camel($_->function)))
            throw new Exception("Method {$_->function} not implemented");

        $this->method = Inflector::id2camel($_->function);
        $this->data = $_->data;
        $this->timestamp = $_->timestamp;

    }

    public function parseMessageKfss($message)
    {
        static $typeToMethod = [
            'ORDER_CALLBACK' => 'setCallbackOrderByKfss',
            'ORDER_STATUS' => 'setStatusOrderByKfss',
            'PROMO' => 'createCoupon',
        ];

        $_ = json_decode($message, true);

        if (isset($_['Info']['Type']) && array_key_exists($_['Info']['Type'], $typeToMethod)) {
            $this->method = $typeToMethod[$_['Info']['Type']];
            $this->data = $_['Data'];
            $this->timestamp = strtotime($_['Info']['Date']);
        }

        if ($this->data === null || empty($this->data)){
            throw new Exception("Data is null or empty");
        }

        if (!method_exists($this, $this->method)){
            throw new Exception("Method {$this->method} not implemented");
        }
    }

    public function actionIndex()
    {

        /** @var SSMessageBus $queue */
        $queue = clone \Yii::$app->frontExchange;
        $queue->queueName = $this->queueName;
        $queue->routingKey = $this->routingKey;

        $queue->messageHandler = function ($id, $message) {

            $this->log("Incoming message. ID: " . $id);
            $this->log("Message body:");
            $this->log($message);

            try {
                $this->parseMessage($message);
            } catch (Exception $e) {
                $this->log("Message decode error");
                $this->log("Exception {$e->getMessage()}");
            }

            try {

                if (call_user_func([$this, $this->method]))
                    return true;
                else
                    return false;

            } catch (Exception $e) {

                $this->log("Message processing error");
                $this->log("Exception: {$e->getMessage()}");

                return false;

            }

        };

        $queue->listen();
    }

    public function log(string $string)
    {
        //$log = fopen(\Yii::getAlias('@app') . '/../../logs/mq/listener.log', 'a+');
        $log = fopen('/tmp/mq_listener.log', 'a+');
        fwrite($log, date('Y-m-d H:i:s') . " " . $string . "\n");
        fclose($log);
    }

}