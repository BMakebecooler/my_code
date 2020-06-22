<?php

namespace common\components\log;

use common\helpers\Msg;
use yii\log\Target;

class TelegramTarget extends Target
{

    /**
     * Длина сообщения
     */
    const MESSAGE_LIMIT = 4000;

    /**
     * Префикс
     * @var string
     */
    public $prefixMessage = '';

    public function init()
    {
        parent::init();
    }

    public function export()
    {
        $messages = array_map([$this, 'formatMessage'], $this->messages);
        foreach ($messages as $message) {
            foreach ($this->splitMessage($message) as $text) {
                $this->sendMessage($text);
            }
        }
    }

    /**
     * @param $text
     * @return mixed
     */
    protected function sendMessage($text)
    {
        return Msg::telegram($text);
    }

    /**
     * @param string $message
     * @return array
     */
    protected function splitMessage($message)
    {
        if (strlen($message) > self::MESSAGE_LIMIT) {
            $i = 0;
            $date = date("Y-m-d H:m:s");
            $messages = array_map(function ($message) use (&$i, $date) {
                return sprintf("%s \n №%d %s \n %s", $this->prefixMessage, ++$i, $date, $message);
            }, str_split($message, self::MESSAGE_LIMIT));
        } else {
            $messages[] = sprintf("%s \n %s", $this->prefixMessage, $message);
        }
        return $messages;
    }
}
