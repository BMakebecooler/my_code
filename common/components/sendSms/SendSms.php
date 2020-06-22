<?php


namespace common\components\sendSms;

use Exception;

interface SendSms
{
    /**
     * Send Sms
     *
     * @param string $phone
     * @param string $statusCode
     *
     * @throws Exception
     * @return bool
     */
    public function sendSms(string $phone, string $text);

    /**
     * Send Response
     *
     * @return void
     */
    public function state();
}
