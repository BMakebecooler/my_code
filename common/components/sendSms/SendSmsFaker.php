<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 20/02/2019
 * Time: 11:39
 */

namespace common\components\sendSms;

use yii\base\Component;

class SendSmsFaker extends Component implements SendSms
{

    public $services;

    public function sendSms(string $phone, string $text)
    {
        // TODO: Implement sendSms() method.
    }


    public function state()
    {
        // TODO: Implement state() method.
    }
}
