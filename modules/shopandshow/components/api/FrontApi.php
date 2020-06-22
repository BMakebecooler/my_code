<?php


namespace modules\shopandshow\components\api;
use modules\shopandshow\components\api\base\ApiBase;
use modules\shopandshow\components\api\base\ApiResponse;
use yii\httpclient\Client;


/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 01.06.17
 * Time: 20:56
 */


class FrontApi extends ApiBase
{

    protected $baseUrl;

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function setBaseUrl($baseUrl)
    {
        return $this->baseUrl = $baseUrl;
    }

    /**
     * @param string $apiMethod - вызываемый метод, список приведен далее
     * @param array $params - параметры соответствующие методу запроса
     * @param string $method
     * @return ApiResponse
     */
    public function send($apiMethod, array $params = [], $method = 'GET')
    {
        $method = strtolower($method);
        $apiUrl = $this->getBaseUrl() . $apiMethod;

        if ($method === 'get') {
            $apiUrl .= '?' . http_build_query($params);
        }

        $client = new Client([
            /*            'requestConfig' => [
                            'format' => Client::FORMAT_JSON
                        ]*/
        ]);

        $httpRequest = $client->createRequest()
            ->setMethod($method)
            ->setUrl($apiUrl)
//            ->setFormat(Client::FORMAT_JSON)
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