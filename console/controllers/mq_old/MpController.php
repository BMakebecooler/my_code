<?php

namespace console\controllers\mq;

use yii\base\Exception;

/**
 * Class MpController
 *
 * @package console\controllers\mq
 */
class MpController extends ListenerController
{

    public $exchangeName = 'MP';

    public $queueName = 'mp.onair';
    public $routingKey = 'mp.onair.lot';

}