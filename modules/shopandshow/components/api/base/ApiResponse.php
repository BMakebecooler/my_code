<?php
namespace modules\shopandshow\components\api\base;

use v3toys\v3project\api\Api;
use yii\base\Component;
use yii\httpclient\Request;
use yii\httpclient\Response;

/**
 * Описание общих полей запросов
 *
 * @property bool isOk read-only
 *
 * @package modules\shopandshow\components\api
 */
class ApiResponse extends Component
{
    /**
     * @var bool Ответ апи считается ошибочным или нет
     */
    public $isError = false;

    /**
     * @var ApiBase
     */
    public $api;

    /**
     * @var string запрошеный метод апи
     */
    public $apiMethod;

    /**
     * @var Request
     */
    public $httpClientRequest;

    /**
     * @var Response
     */
    public $httpClientResponse;


    /**
     * @var array ответ апи с которым и надо работать
     */
    public $data;

    /**
     * @var string сообщение об ошибке
     */
    public $errorMessage = '';

    /**
     * @var string код об ошибке
     */
    public $errorCode;

    /**
     * @var array данные об ошибке
     */
    public $errorData;


    /**
     * @return bool
     */
    public function getIsOk()
    {
        return !$this->isError;
    }

    /**
     * Небольшая логика обработки ответа
     */
    public function init()
    {
        try
        {
            $this->data = $this->httpClientResponse->data;
        } catch (\Exception $e)
        {
            \Yii::error($this->httpClientResponse->content, self::className());

            $this->isError       = true;
            $this->errorMessage  = 'Не удалось отформатировать ответ от сервера';
            $this->errorCode     = $this->httpClientResponse->statusCode;
            $this->errorData     = $this->data;

            return;
        }

        if (!$this->httpClientResponse->isOk)
        {
            \Yii::error($this->httpClientResponse->content, self::className());

            $this->isError       = true;
            $this->errorMessage  = $this->api->getMessageByStatusCode($this->httpClientResponse->statusCode);
            $this->errorCode     = $this->httpClientResponse->statusCode;
            $this->errorData     = $this->data;

            return;
        }
    }
}