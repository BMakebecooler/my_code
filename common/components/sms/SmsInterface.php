<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 20/02/2019
 * Time: 11:26
 */

namespace common\components\sms;


interface SmsInterface
{

    public function sendSms($phone, $text,
                            $saveInfo = true,
                            $type = null,
                            $forUserId = null,
                            $mustSentAt = null,
                            $options = [],
                            $fUserId = null
    );

    public function sendSmsFromFuser($phone, $text, $fUserId = null, $options = [], $type = null);

    public function canRequest($phone, $fUserId = null);

}