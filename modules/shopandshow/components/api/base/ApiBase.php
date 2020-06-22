<?php
/**
 * Базовый класс для апи
 * User: ubuntu5
 * Date: 14.03.17
 * Time: 17:33
 */

namespace modules\shopandshow\components\api\base;


use common\helpers\ArrayHelper;
use yii\base\Component;
use yii\httpclient\Client;

abstract class ApiBase extends Component
{

    /**
     * @var
     */
    public $timeout = 2;


    /**
     * Коды ответа на запрос
     * @var array
     */
    static public $errorStatuses = [
        '404' => 'Запрошенный метод апи не существует',
        '500' => 'Произошла внутренняя ошибка сервиса во время обработки',
    ];


    /**
     * @return mixed
     */
    abstract function getBaseUrl();


    /**
     * @param $httpStatusCode
     *
     * @return string
     */
    public function getMessageByStatusCode($httpStatusCode)
    {
        return (string)ArrayHelper::getValue(static::$errorStatuses, (string)$httpStatusCode);
    }

    /**
     * @param string $apiMethod - вызываемый метод, список приведен далее
     * @param array $params - параметры соответствующие методу запроса
     * @param string $method
     * @return ApiResponse
     *
     * TODO: тип контента (json, plain string) должен задаваться параметром - vkharkov
     *
     */
    public function send($apiMethod, array $params = [], $method = 'GET')
    {
        $method = strtolower($method);
        $apiUrl = $this->getBaseUrl() . $apiMethod;

        if ($method === 'get') {
            $apiUrl .= '?' . http_build_query($params);
        }

        $client = new Client([
            'requestConfig' => [
                'format' => Client::FORMAT_JSON
            ]
        ]);

        $httpRequest = $client->createRequest()
            ->setMethod($method)
            ->setUrl($apiUrl)
            ->addHeaders(['Accept' => 'application/json'])
            ->setOptions([
                'timeout' => $this->timeout
            ]);

        if ($method === 'post') {
            $httpRequest->setData($params);
        }

        $httpResponse = $httpRequest->send();

        $apiResponse = new ApiResponse([
            'api' => $this,
            'httpClientRequest' => $httpRequest,
            'httpClientResponse' => $httpResponse,
            'apiMethod' => $apiMethod,
        ]);

        return $apiResponse;
    }

}