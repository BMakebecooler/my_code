<?php

namespace common\components;

use common\helpers\Common;
use common\helpers\Strings;
use common\models\User;
use skeeks\cms\base\Component;
use yii\di\Instance;
use yii\httpclient\Client;

/**
 * Класс работы с KFSS API
 * Class KfssLkApiV2
 * @package common\components
 */
class KfssLkApiV2 extends Component
{

    const TIMEOUT = 2;
    const MILLION_PROMO_INIT_CODE = '#MIL_INIT#';

    private $response = [
        'success' => false,
        'message' => '',
        'data' => []
    ];

    /**
     * @var Client $httpClient
     */
    public $httpClient;

    public $baseUrl;
    public $username;
    public $password;

    protected $debugMode = false;

    public $isDisable = true;

    //* Времянка *//

    const ORDER_STATUS_CREATING = 'creating';
    const ORDER_STATUS_RESERVE = 'reserve';
    const ORDER_STATUS_COMPLETE = 'complete';
    const ORDER_STATUS_CANCELED = 'canceled';
    const ORDER_STATUS_RETURN = 'return';

    public static $ordersStatusNames = [
        self::ORDER_STATUS_CREATING => 'Создается',
        self::ORDER_STATUS_RESERVE => 'Резервирование товара для Сайта',
        self::ORDER_STATUS_COMPLETE => 'Вручен клиенту',
        self::ORDER_STATUS_CANCELED => 'Отменен',
        self::ORDER_STATUS_RETURN => 'Возврат',
    ];

    //* /Времянка *//

    public function init()
    {
        if (is_array($this->httpClient)) {
            $this->httpClient = Instance::ensure($this->httpClient);

            $this->httpClient->baseUrl = $this->baseUrl;
        }

        parent::init();

    }

    public function getUserByPhone($phone)
    {
        return $this->_call('GET', 'findclient/?phone=' . $phone);

        $kfssUser = $this->_call('GET', 'findclient/?phone=' . $phone);
        return $kfssUser ? $this->_correctUserDataResponse($kfssUser) : false;
    }

    public function getUserDataByPhone($phone)
    {
        $kfssUser = [];

        if ($phone = Strings::getPhoneClean($phone)) {
            $kfssUserResponse = $this->getUserByPhone($phone);
            if (isset($kfssUserResponse['code'])){
                if ($kfssUserResponse['code'] == \common\models\User::KFSS_STATUS_OK){
                    $kfssUser = $kfssUserResponse['data'];
                }
            }
        }

        return $kfssUser;
    }

    public function getUserById($id)
    {
        return $this->_call('GET', 'client/' . $id);

        $kfssUser = $this->_call('GET', 'client/' . $id);
        return $kfssUser ? $this->_correctUserDataResponse($kfssUser) : false;
    }

    //[DEPRECATED] В связи со сменой формата ответа от КФСС уже не актуально
    private function _correctUserDataResponse($kfssUser)
    {
        if ($kfssUser) { //Пользователь нашелся //должен находиться даже если он заблокирован
            $kfssUser['_status'] = User::KFSS_STATUS_UNKNOWN;

            if (!empty($kfssUser['guid'])) {
                $kfssUser['_status'] = User::KFSS_STATUS_OK;
            } else {
                if (isset($kfssUser['error'])) {
                    switch ($kfssUser['error']['code']) {
                        case 403:
                            $kfssUser['_status'] = User::KFSS_STATUS_BLOCKED;
                            break;
                        case 404:
                            $kfssUser['_status'] = User::KFSS_STATUS_NOT_FOUND;
                            break;
                    }
                }
            }
        }

        return $kfssUser;
    }

    //Регистрация клиента
    public function signup($config)
    {
        if (empty($config['phone'])) {
            return false;
        }

        $data = [
            'phone' => $config['phone']
        ];

        $response = $this->_call('POST', 'client/', $data);

        if (isset($response['code']) && in_array($response['code'], [User::KFSS_STATUS_OK, User::KFSS_STATUS_NOT_FOUND, User::KFSS_STATUS_BLOCKED]) && !empty($response['data']['id'])) {
            $kfssUser = $this->getUserById($response['data']['id']);
        }

        return $kfssUser ?? false;
    }

    //Возвращает заказы клиента в указанном статусе
    //Скорее всего временный метод пока нет нормального метода АПИ с фильтрами в запросе
    //TODO Учесть скорое обновление АПИ КФСС где появится постраничный вывод списка заказов!
    public function getUserOrdersWithStatus($userIdKfss, $status)
    {
        $orders = [];

        if (!empty(self::$ordersStatusNames[$status])) {
            $response = $this->getUserOrders($userIdKfss);
            $statusName = self::$ordersStatusNames[$status];

            if (isset($response['code']) && $response['code'] == 0 && !empty($response['data'])) {
                $ordersAll = $response['data'];
                $orders = array_filter($ordersAll, function ($order) use ($statusName) {
                    return $order['status'] == $statusName;
                });
            }
        }

        return $orders;
    }

    public function getUserOrders($userIdKfss)
    {
        return $this->_call('GET', "clientorder/{$userIdKfss}/");
    }

    //* HUNT FOR MILLION *//

    //Принять участие для текущего пользователя
    public function initMillionPromo($kfssUserId)
    {
        return $this->addMillionPromoCode(self::MILLION_PROMO_INIT_CODE, $kfssUserId);
    }

    public function addMillionPromoCode($code, $kfssUserId)
    {
        $data = [
            'clientId' => $kfssUserId,
            'code' => $code,
        ];
        return $this->_call('POST', "lotteryactioncode", $data);
    }

    public function getMillionPromoUserBalance($kfssUserId)
    {
        $data = [
            'clientId' => $kfssUserId,
        ];
        //Почему получение баланса идет через POST запрос непонятно
        return $this->_call('POST', "lotteryactionbalance", $data);
    }

    public function getMillionPromoRating($type = 'M')
    {
        $data = [
            "rateType" => $type == 'W' ? 'W' : 'M',
        ];
        return $this->_call('POST', "lotteryactionrating", $data);
    }

    //* /HUNT FOR MILLION *//

    private function _call($method, $url, $params = null)
    {
        $request = $this->httpClient->createRequest()
            ->setMethod($method)
            ->setFormat(Client::FORMAT_JSON)
            ->setUrl($url)
            ->setOptions([
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
            ])
//        ->setOptions([
//            'timeout' => self::TIMEOUT,
//        ])
//            ->setData($params)
            ->addHeaders(['Authorization' => 'Basic ' . base64_encode("{$this->username}:{$this->password}")]);

        if (!empty($params)) {
            $request->setData($params);
        } else {
            //Ибо ругается если нет данных и нет указания что их блина 0
            $request->addHeaders(['content-length' => 0]);
        }

        Common::startTimer("kfssLkApiV2::{$method}-{$url}");

        $response = $request->send();
        $responseData = $response->getData();

        $time = Common::getTimerTime("kfssLkApiV2::{$method}-{$url}", false);

        if ($time > self::TIMEOUT) {
            \Yii::error("kfssLkApiV2::[{$method}]{$url} time = {$time} sec", "kfsslkapiv2-long-response");
        }

        \Yii::error("[Time={$time}] Request kfssLkApiV2 [{$method}] {$url}, data " .
            print_r($params ?: '<EMPTY>', true) . ", Response [{$response->getStatusCode()}]: " .
            print_r($responseData ?: '<EMPTY>', true), 'common\components\kfsslkapiv2');

        if ($response->isOk || in_array($response->getStatusCode(), ['403', '404'])) {
            return $response->getStatusCode() == 204 ? true : $responseData; //Учет кейса когда в ответ нет данных, только статус
        } else {
            \Yii::error("ERROR! Request kfssLkApiV2 [{$method}] {$url}, data " .
                print_r($params ?: '<EMPTY>', true) . ", Response [{$response->getStatusCode()}]: " .
                print_r($responseData ?: '<EMPTY>', true), 'common\components\kfsslkapiv2');
            return false;
        }
    }
}
