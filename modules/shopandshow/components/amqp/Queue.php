<?php

namespace modules\shopandshow\components\amqp;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use yii\base\Exception;
use skeeks\cms\base\Component;

class Queue extends Component
{

    const EXCHANGE_DIRECT = 'direct';
    const EXCHANGE_TOPIC = 'topic';
    const EXCHANGE_FANOUT = 'fanout';

    public $host = 'localhost';
    public $port = 5672;

    public $user;
    public $password;

    public $exchangeName;
    public $exchangeType;

    /**
     * @var AMQPStreamConnection
     */
    private $connection;
    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * Opens connection and channel
     */
    protected function open()
    {
        if ($this->channel) return;

        $this->connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->password);
        $this->channel = $this->connection->channel();

        $this->channel->exchange_declare($this->exchangeName, $this->exchangeType, false, true, false);


//        $this->channel->queue_declare($this->queueName, false, true, false, false);
//        $this->channel->queue_bind($this->queueName, $this->exchangeName, $this->routingKey);
    }

    public function push($message, $routingKey)
    {

        try {

            $this->open();

            $this->channel->basic_publish(
                new AMQPMessage($message, [ 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT ]),
                $this->exchangeName,
                $routingKey
            );

            $this->close();

            return true;

        } catch (Exception $e) {
            return false;
        }

    }

//
//
//    /**
//     * @inheritdoc
//     */
//    protected function pushMessage($message, $delay, $priority)
//    {
//        if ($delay) {
//            throw new NotSupportedException('Delayed work is not supported in the driver.');
//        }
//        if ($priority !== null) {
//            throw new NotSupportedException('Job priority is not supported in the driver.');
//        }
//        $this->open();
//
//        return null;
//    }
//
//    /**
//     * Listens amqp-queue and runs new jobs.
//     */
//    public function listen()
//    {
//        $this->open();
//        $callback = function($message) {
//            if ($this->handleMessage(null, $message->body)) {
//                $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
//            }
//        };
//        $this->channel->basic_qos(null, 1, null);
//        $this->channel->basic_consume($this->queueName, '', false, false, false, false, $callback);
//        while(count($this->channel->callbacks)) {
//            $this->channel->wait();
//        }
//    }
//
//

    /**
     * Closes connection and channel
     */
    protected function close()
    {
        if (!$this->channel) return;
        $this->channel->close();
        $this->connection->close();
    }
}