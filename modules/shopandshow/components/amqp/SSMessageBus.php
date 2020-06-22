<?php

namespace modules\shopandshow\components\amqp;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

use yii\base\NotSupportedException;
use yii\queue\amqp\Queue as BaseQueue;

/**
 * Amqp Queue
 */
class SSMessageBus extends BaseQueue
{

    public $vhost = '/';

    public $exchangeName;
    public $exchangeType;

    public $queueName;
    public $routingKey;

    // time in seconds consumer will wait message (0 - infinite: daemon mode)
    public $waitTimeout = 0;
    // handler, checks consumer need to stop
    public $stopHandler;

    /** TODO: логирование, профилирование на компанентах Yii */

    public function listen()
    {
        $this->open();
        $callback = function (AMQPMessage $payload) {
            if ($this->handleMessage(null, $payload->body, 300, 1)) {
                $payload->delivery_info['channel']->basic_ack($payload->delivery_info['delivery_tag']);

                $this->handleStop($payload);
            }
        };
        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume($this->queueName, '', false, false, false, false, $callback);

        while (count($this->channel->callbacks)) {
            try {
                $this->channel->wait(null, false, $this->waitTimeout);
            } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $e) {
                if ($this->handleStop()) {
                    // end loop
                    break;
                }
            }
        }
    }

    /**
     * проверяет, надо ли останавливать процесс (см. stopHandler)
     * @param AMQPMessage|null $payload
     * @return bool
     */
    public function handleStop(AMQPMessage $payload = null)
    {
        if ($this->stopHandler && is_callable($this->stopHandler)) {
            // stop listener
            if (call_user_func($this->stopHandler)) {
                if ($payload == null) {
                    $this->channel->basic_cancel('');
                } else {
                    $payload->delivery_info['channel']->basic_cancel($payload->delivery_info['consumer_tag']);
                }

                return true;
            }
        }

        return false;
    }

    public function push($message)
    {
        return $this->pushMessage(json_encode($message), 300, null, null);
    }

    protected function pushMessage($message, $ttr, $delay, $priority)
    {
        if ($delay) {
            throw new NotSupportedException('Delayed work is not supported in the driver.');
        }
        if ($priority !== null) {
            throw new NotSupportedException('Job priority is not supported in the driver.');
        }

        $_message = new AMQPMessage($message, [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            'content_type' => 'application/json',
            'content_encoding' => 'utf-8'
        ]);

        $_message->set('application_headers',

            /** TODO: сделать класс для работы с сообщениями. Автоматическое создание заголовков, валидация по json-schema... */

            /** Set HEADERS */
            new AMQPTable([
                'Host' => 'new.shopandshow.ru'
            ])

        );

        $this->open();
        $this->channel->basic_publish(
            $_message,
            $this->exchangeName,
            $this->routingKey
        );

        return null;
    }


    protected function open()
    {
        if ($this->channel) return;
        $this->connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->password, $this->vhost);
        $this->channel = $this->connection->channel();
        $this->channel->exchange_declare($this->exchangeName, $this->exchangeType, false, true, false);

        /** Когда класс используется в качестве консюмера - создать очереди */
        if (!empty($this->queueName) && !empty($this->routingKey)) {
            $this->channel->queue_declare($this->queueName, false, true, false, false);
            $this->channel->queue_bind($this->queueName, $this->exchangeName, $this->routingKey);
        }

    }

}