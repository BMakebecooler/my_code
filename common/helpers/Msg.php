<?php

/**
 * Хэлпер для отправки сообщений
 * User: ubuntu5
 * Date: 22.06.17
 * Time: 19:14
 */

namespace common\helpers;


class Msg
{

    /**
     * канал для общих сообщений, которые вызываются прямо в коде
     */
    const CHANNEL_DEFAULT = '-1001288101558';

    /**
     * сообщения с ошибками
     */
    const CHANNEL_ERRORS = '-1001226407996';

    /**
     * Отправить сообщение через телеграмм
     * @param string $msgText
     * @param string $channel
     * @return mixed
     */
    public static function telegram($msgText = '', $channel = self::CHANNEL_DEFAULT)
    {
        return \Yii::$app->telegramBot->sendMessage($channel, $msgText);
    }

    /**
     * Отправить сообщение об ошибке в телеграм
     * @param string $msgText
     * @return mixed
     */
    public static function telegramError($msgText = '')
    {
        return self::telegram($msgText, self::CHANNEL_ERRORS);
    }
}