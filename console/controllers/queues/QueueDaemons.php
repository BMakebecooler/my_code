<?php

/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 27.09.17
 * Time: 20:10
 */

namespace console\controllers\queues;

use yii\base\Component;

class QueueDaemons extends Component
{

    /**
     * @var
     */
    public $queues = [];

    public function init()
    {
        parent::init();

        foreach ($this->queues as $queue => $channel) {
            if (
                !isset($channel['queueName']) &&
                !isset($channel['routingKey']) &&
                !isset($channel['queue']) &&
                !isset($channel['exchangeName']) &&
                !isset($channel['jobClass'])
            ) {
                throw new \Exception('Укажите queue, queueName, exchangeName, routingKey и jobClass!');
            }
        }
    }

    /**
     * @return array
     */
    public function getQueues()
    {
        return $this->queues;
    }


    public function startListener()
    {

    }

}