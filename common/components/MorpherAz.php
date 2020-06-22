<?php

/**
 * @author Arkhipov Andrei <arhan89@gmail.com>
 * @copyright (c) K-Gorod
 * Date: 13.06.2019
 * Time: 10:13
 */

namespace common\components;


use common\helpers\ArrayHelper;
use yii\base\Component;
use yii\base\Exception;
use yii\httpclient\Client;
use yii\web\HttpException;

class MorpherAz extends Component
{
    /**
    "им": "nominative",
    "рд": "genitive",
    "дт": "dative",
    "вн": "accusative",
    "тв": "ablative",
    "пр": "prepositional",
     */
    const NOMINATIVE = 'nominative';
    const GENITIVE = 'genitive';
    const DATIVE = 'dative';
    const ACCUSATIVE = 'accusative';
    const ABLATIVE = 'ablative';
    const PREPOSITIONAL = 'prepositional';

    const SINGULAR = 'singular';
    const PLURAL = 'plural';

    /** @var Client $client */
    public $client;
    public $token;
    public $data;
    public $baseUrl = 'http://188.225.82.3:3000';

    public function init()
    {
        if (empty($this->client)) {
            $this->client = (new Client([
                'baseUrl' => $this->baseUrl
            ]));
        }
    }

    /**
     * @param string $word
     * @return $this
     * @throws Exception
     * @throws HttpException
     */
    public function declension(string $word)
    {
        $this->data = $this->fetchData('declension', ['word' => $word]);
        return $this;
    }

    /**
     * Запрос на сервер.
     * @param $url
     * @param $params
     * @return mixed
     * @throws Exception
     * @throws HttpException
     */
    private function fetchData($url, $params)
    {
        if ($this->token) {
            $params['token'] = $this->token;
        }
        $response = $this->client
            ->get($url, $params)
            ->send();

        if ($response->isOk) {
            $data = $response->data;
        } else {
            throw new HttpException($response->statusCode, 'Morpher service error');
        }

        if (key_exists('code', $data)) {

            $code = ArrayHelper::getValue($data, 'code');
            $message = ArrayHelper::getValue($data, 'message');

            if (empty($message)) {

                switch ($code) {
                    case 1 :
                        $message = 'Превышен лимит на количество запросов в сутки. Перейдите на следующий тарифный план.';
                        break;
                    case 2 :
                        $message = 'Превышен лимит на количество одинаковых запросов в сутки. Реализуйте кеширование.';
                        break;
                    case 3 :
                        $message = 'IP заблокирован.';
                        break;
                    case 4 :
                        $message = 'Склонение числительных в GetXml не поддерживается. Используйте метод Propis.';
                        break;
                    case 5 :
                        $message = 'Не найдено русских слов.';
                        break;
                    case 6 :
                        $message = 'Не указан обязательный параметр s.';
                        break;
                    case 7 :
                        $message = 'Необходимо оплатить услугу.';
                        break;
                    case 8 :
                        $message = 'Пользователь с таким ID не зарегистрирован.';
                        break;
                    case 9 :
                        $message = 'Неправильное имя пользователя или пароль.';
                        break;
                    default :
                        $message = 'Неизвестный тип ошибки попробуйте позже.';
                }
            }
            throw new Exception("Morpher service error (code: {$code}): {$message}");
        }
        return $data;
    }

}