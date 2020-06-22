<?php

namespace common\components;


use common\helpers\Common;
use common\helpers\Strings;
use yii\base\Component;
use yii\di\Instance;
use yii\httpclient\Client;

class SmscHlr extends Component
{
    const TIMEOUT = 4;

    public $httpClient;
    public $baseUrl;
    public $username;
    public $password;

    public $delayBeforeFirstCheck;
    public $delayBetweenStatusChecks;
    public $statusCheckAttemptsNum;

    const RESPONSE_CODE_WAIT_SEND = -1;
    const RESPONSE_CODE_WAIT_OPERATOR = 0;
    const RESPONSE_CODE_DELIVERED = 1;
    const RESPONSE_CODE_READED = 2;

    const ERROR_CODE_SEND_INVALID_NUMBER = 7;
    const ERROR_CODE_SEND_CANT_DELIVER = 8;

    public function init()
    {
        if (is_array($this->httpClient)) {
            $this->httpClient = Instance::ensure($this->httpClient);

            $this->httpClient->baseUrl = $this->baseUrl;
        }
        parent::init();
    }

    public function send($phone)
    {
        $phoneProper = \common\helpers\Strings::getPhoneClean($phone, true);

        if ($phoneProper) {
            return $this->_call('GET', 'send.php', ['phones' => $phoneProper, 'hlr' => 1]);
        }

        return false;
    }

    public function check($phone, $msgId)
    {
        $phoneProper = \common\helpers\Strings::getPhoneClean($phone, true);

        if ($phoneProper && (int)$msgId) {
            return $this->_call('GET', 'status.php', ['phone' => $phoneProper, 'id' => $msgId]);
        }

        return false;
    }

    public function getPhoneAvailabilityForCall($phone)
    {
        $available = true;
        $message = '';

        $phone = Strings::getPhoneClean($phone, true);

        if ($phone){
            $responseSend = $this->send($phone);

            //Кейсы:
            //В общем случае не должно быть ключа error_code и должен быть ID запроса, тогда идем дальше к проверке статуса
            //Для городских номеров и мегафона сразу приходит ошибька о невозможности доставки (error_code=8) - считаем что все доступно

            if ($responseSend) {
                if (isset($responseSend['error_code']) && $responseSend['error_code'] == self::ERROR_CODE_SEND_CANT_DELIVER) {
                    //Городской или мегафон - сразу ок
                    $available = true;
                }elseif (isset($responseSend['error_code']) && $responseSend['error_code'] != self::ERROR_CODE_SEND_CANT_DELIVER) {
                    //Есть ошибка и она не "нормальная"
                    $available = false;
                    $message = 'Указанный номер телефона недоступен, пожалуйста укажите другой или исправьте этот';
                }elseif (!isset($responseSend['error_code']) && !empty($responseSend['id'])) {
                    $msgId = $responseSend['id'];

                    if ($this->delayBeforeFirstCheck) {
                        //почему то не робит если через параметр
//                        \Yii::error("Sleep for {$this->delayBeforeFirstCheck}mksec", 'common\components\smschlr');
                        //usleep($this->delayBeforeFirstCheck);
                        sleep(1);
                    }

                    for ($attemptNum = 1; $attemptNum <= $this->statusCheckAttemptsNum && $attemptNum<=10; $attemptNum++){
                        $responseGetStatus = $this->check($phone, $msgId);

                        if ($responseGetStatus){

                            //Проверяем на то что отправка уже состоялась и имеет смысл проверять статусы отправки
                            if (isset($responseGetStatus['status'])
                                && $responseGetStatus['status'] != self::RESPONSE_CODE_WAIT_SEND
                                && $responseGetStatus['status'] != self::RESPONSE_CODE_WAIT_OPERATOR){
                                //отправка состоялась, проверяем нормально
                                if (isset($responseGetStatus['status']) && in_array($responseGetStatus['status'], [self::RESPONSE_CODE_DELIVERED, self::RESPONSE_CODE_READED])) {
                                    //Все ок, прекращаем попытки
                                    $available = true;
                                    break;
                                }else{
                                    $available = false;
                                    $message = 'Указанный номер телефона недоступен, пожалуйста укажите другой.';
                                    break;
                                }

                            }else{
                                //отправка не остоялась, дальше пойдет задержка и следующая попытка
                            }

                        }else{
                            //\Yii::error("Нет ответа от АПИ HLR для получения списка");
                        }

                        if ($this->delayBetweenStatusChecks) {
                            //почему то не робит если через параметр
//                            \Yii::error("Sleep for {$this->delayBetweenStatusChecks}mksec", 'common\components\smschlr');
                            //usleep($this->delayBetweenStatusChecks);
                            sleep(1);
                        }
                    }
                }
            }else{
                $message = 'Возникла ошибка, попробуйте повторить попытку позже.';
            }
        }else{
            $available = false;
            $message = 'Указанный номер телефона недоступен, пожалуйста укажите другой или исправьте этот.';
        }

        return [
            'available' => $available,
            'message' => $message,
        ];
    }

    private function _call($method, $url, $params = [])
    {
        $params = array_merge($params, [
            'login' => $this->username,
            'psw' => $this->password,
            'fmt' => 3, //JSON
        ]);

        $request = $this->httpClient->createRequest()
            ->setMethod($method)
            ->setFormat(Client::FORMAT_RAW_URLENCODED)
            ->setUrl($url);
//            ->setOptions([
//                CURLOPT_SSL_VERIFYHOST => false,
//                CURLOPT_SSL_VERIFYPEER => false,
//            ])
//        ->setOptions([
//            'timeout' => self::TIMEOUT,
//        ]);

        if (!empty($params)) {
            $request->setData($params);
        }else{
            //Ибо ругается если нет данных и нет указания что их блина 0
            $request->addHeaders(['content-length' => 0]);
        }

        Common::startTimer("SmscHlr::{$method}-{$url}");

        $response = $request->send();
        $responseData = $response->getData();

        $time = Common::getTimerTime("SmscHlr::{$method}-{$url}", false);

        if ($time > self::TIMEOUT){
            \Yii::error("SmscHlr::[{$method}]{$url} time = {$time} sec", "smschlr-long-response");
        }

        $ts = time();

        \Yii::error("[Time={$time} | {$ts}] Request SmscHlr [{$method}] {$url}, data " . print_r($params ?: '<EMPTY>', true) . ", Response [{$response->getStatusCode()}]: " . print_r($responseData?: '<EMPTY>', true), 'common\components\smschlr');

        if ($response->isOk) {
            return $response->getStatusCode() == 204 ? true : $responseData; //Учет кейса когда в ответ нет данных, только статус
        } else {
            \Yii::error("ERROR! Request SmscHlr [{$method}] {$url}, data " . print_r($params ?: '<EMPTY>', true) . ", Response [{$response->getStatusCode()}]: " . print_r($responseData?: '<EMPTY>', true), 'common\components\smschlr');
            return false;
        }
    }
}

