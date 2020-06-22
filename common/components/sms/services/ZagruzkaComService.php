<?php

namespace common\components\sms\services;

use common\helpers\Strings;
use Exception;
use lowbase\sms\AbstractService;
use lowbase\sms\models\Sms;

/**
 * Class ZagruzkaComService
 * @link https://smsinfostat.zagruzka.com/partner/
 * @link https://docs.zagruzka.com/pages/viewpage.action?pageId=327775
 * Zagruzka Support <help@zagruzka.com>
 * @package lowbase\sms
 */
class ZagruzkaComService extends AbstractService
{
    const SEND_SMS_URL = 'http://lk.zagruzka.com:9080/shopandshow_mob';

    const GET_SMS_STATUS_URL = 'http://gate.iqsms.ru/status/';
    const GET_BALANCE_URL = 'http://api.iqsms.ru/messages/v2/balance/';

    /**
     * Имя отправителя. Сообщение абоненту будет отправлено с номера, указанного в данном параметре.
     * Допустимая длина 2-11 символов. Допустимые символы:0...9a...zA...Z!@#$%^&*()/{}';:,+-_ и пробел.
     *
     * Данный параметр не является обязательным. Если Контент-провайдер не передает в запросе данный параметр, то сообщение будет отправлено
     *   абоненту с номера по умолчанию (настройка на стороне Агрегатора для сервиса Контент-провайдера).
     *
     * !!!!! Использование данного параметра не доступно для Контент-провайдера по умолчанию, функционал может быть включен после согласования с Агрегатором.
     * В этом случае для сервиса Контент-провайдера настраивается список разрешенных имен отправителей, либо включается функционал динамической подписи.
     *
     * @var string
     */
    public $source = '';

    public $statusMap = [
        'queued' => Sms::STATUS_QUEUED,
        'smsc submit' => Sms::STATUS_QUEUED,
        'delivered' => Sms::STATUS_DELIVERED,
        'delivery error' => Sms::STATUS_FAILED,
        'smsc reject' => Sms::STATUS_FAILED,
        'incorrect id' => Sms::STATUS_UNKNOWN,
    ];

    /**
     * Send sms
     * @param $phone
     * @param $text
     * @param null $must_sent_at
     * @param array $options
     * @return array
     */
    public function sendSms($phone, $text, $must_sent_at = null, $options = [])
    {
        if ($must_sent_at) {
            $options['scheduleTime'] = $must_sent_at;
        }

        $result = $this->sendRequest(self::SEND_SMS_URL, array_merge($options, [
            'serviceId' => $this->login,
            'pass' => $this->password,
            'source' => $this->source,
            'message' => $text,
            'clientId' => $phone,
        ]));

        if (substr_count($result, 'OK')) {
            //success
            return [
                'status' => Sms::STATUS_SENT,
                'id' => Strings::onlyInt($result),
                'answer' => $result
            ];

        } else {
            return [
                'status' => Sms::STATUS_FAILED,
                'answer' => $result
            ];
        }
    }

    /**
     * Send Request
     *
     * @param $requestUrl
     * @param null $options
     * @return mixed
     */
    static public function sendRequest($requestUrl, $options = null)
    {
        try {

            mb_internal_encoding("UTF-8");

            $ch = curl_init();

            if (false === $ch) {
                throw new Exception('failed to initialize');
            }

            $requestUrl .= '?' . http_build_query($options);

            curl_setopt($ch, CURLOPT_URL, $requestUrl);
            curl_setopt($ch, CURLOPT_HTTPGET, $options);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($ch);
            curl_close($ch);

            if (false === $result) {
                throw new Exception(curl_error($ch), curl_errno($ch));
            }

            return $result;

        } catch (Exception $e) {

            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
                E_USER_ERROR);

        }
    }

    /**
     * Get sms status by id
     *
     * @param $provider_sms_id
     * @param array $options
     * @return array
     */
    public function getSmsStatus($provider_sms_id, $options = [])
    {

        return;
        $result = $this->sendRequest(self::GET_SMS_STATUS_URL, array_merge($options, [
            'login' => $this->login,
            'password' => $this->password,
            'id' => $provider_sms_id,
        ]));

        if (substr_count($result, '=')) {
            $status = explode('=', $result)[1];
            $status = in_array($status, array_keys($this->statusMap)) ? $this->statusMap[$status] : Sms::STATUS_UNKNOWN;

            return [
                'status' => $status,
                'answer' => $result
            ];

        } else {

            return [
                'status' => Sms::STATUS_UNKNOWN,
                'answer' => $result
            ];
        }
    }


    /**
     * Get account status
     *
     * @param array $options
     * @return bool|float
     */
    public function getBalance($options = [])
    {

        return;

        $result = $this->sendRequest(self::GET_BALANCE_URL, array_merge($options, [
            'login' => $this->login,
            'password' => $this->password,
        ]));

        if (substr_count($result, 'RUB')) {
            $balance = explode(';', $result);
            if (isset($balance[1])) {
                return (float)$balance[1];
            }
        }

        return false;
    }

}
