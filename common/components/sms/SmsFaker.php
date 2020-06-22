<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 20/02/2019
 * Time: 11:39
 */

namespace common\components\sms;


use yii\base\Component;

class SmsFaker extends Component implements SmsInterface
{

    public $services;

    public function sendSms($phone, $text,
                            $saveInfo = true,
                            $type = null,
                            $forUserId = null,
                            $mustSentAt = null,
                            $options = [],
                            $fUserId = null
    )
    {
        // TODO: Implement sendSms() method.
    }

    public function sendSmsFromFuser($phone, $text, $fUserId = null, $options = [], $type = null)
    {
        // TODO: Implement sendSmsFromFuser() method.
    }

    public function canRequest($phone, $fUserId = null)
    {
        // TODO: Implement canRequest() method.
    }
}