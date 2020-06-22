<?php

namespace common\components\captcha;

use yii\base\Component;
use Yii;

class GoogleCaptcha extends Component
{
    public $publicKey;

    public $secretKey;

    protected $apiURL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Проверка капчи
     *
     * @param string $token
     * @param string $action
     *
     * @return bool
     */
    public function verifyCaptcha($token = null, $action = null)
    {

        if (!$token) {
            return false;
        }

        if (!$action) {
            return false;
        }

        $params = [
            'secret' => $this->secretKey,
            'response' => $token,
            'remoteip' => Yii::$app->request->userIP
        ];

        $ch = curl_init($this->apiURL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        if (!empty($response)) {
            $decodedResponse = (array)json_decode($response);
        } else {
            $decodedResponse = null;
        }

        if ($decodedResponse) {
            if ($decodedResponse['success'] && $decodedResponse['action'] == $action && $decodedResponse['score'] > 0) {
                return true;
            } else {
                return false;

                //todo времянка пока не исправят на сторооне фронта
//                if (count($decodedResponse['error-codes']) == 1 && $decodedResponse['error-codes'][0] == 'timeout-or-duplicate') {
//                    return true;
//                } else {
//                    return false;
//                }
            }
        } else {
            return false;
        }

    }

}