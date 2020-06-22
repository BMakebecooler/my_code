<?php

namespace common\components;

use common\helpers\Common;
use common\models\ShopOrder;
use modules\shopandshow\models\shop\ShopOrderStatus;
use yii\base\Component;
use yii\di\Instance;
use yii\httpclient\Client;

/**
 * Класс работы с KFSS API
 * Class KfssApi
 * @package common\components
 */
class KfssAlfaApiV1 extends Component
{
    const TIMEOUT = 5;
    const PAGE_VIEW_TYPE_DESKTOP = 'DESKTOP';
    const PAGE_VIEW_TYPE_MOBILE = 'MOBILE';

    const ORDER_PAYMENT_ERRORCODE_SUCCESS  = 2; //Статус "ошибки" при котором  считается что платеж успешно осуществлен

    /**
     * @var Client $httpClient
     */
    public $httpClient;

    public $baseUrl;
    public $username;
    public $password;

    public function init()
    {
        if (is_array($this->httpClient)) {
            $this->httpClient = Instance::ensure($this->httpClient);
            $this->httpClient->baseUrl = $this->baseUrl;
        }

        parent::init();
    }

    public function registerOrderPayment($order)
    {
        if (is_numeric($order)) {
            $order = ShopOrder::findOne($order);
        }

        if ($order && $order->order_number) {
            $orderUrl = $order->getPublicUrl();

            $data = [
                'orderId' => $order->order_number,
                'returnUrl' => $orderUrl,
                'failUrl' => $orderUrl,
//                'pageView' => \Yii::$app->mobileDetect->isMobile() ? self::PAGE_VIEW_TYPE_MOBILE : self::PAGE_VIEW_TYPE_DESKTOP,
                'pageView' => self::PAGE_VIEW_TYPE_DESKTOP, //По информации Альфы страница оплаты адаптивная и этот параметр используется для каких то других целей
            ];

            $responseData = $this->_call('POST', 'alfabankorderregister', $data);

            if ($responseData && isset($responseData['errorCode']) && $responseData['errorCode'] == 0) {
                if ($responseData['formUrl']) {
                    return  $responseData['formUrl'];

                    //Если надо будет тут же сохранять путь к форме
                    $order->comments =  $responseData['formUrl'];
                    if (!$order->save()) {
                        \Yii::error("ERROR! Can't save order formUrl [{$order->order_number} | {$order->id}]. Errors: " . var_export($order->getErrors(), true), 'common\components\kfssalfaapiv1');
                        return $order->comments;
                    }
                }else{
                    \Yii::error("ERROR! Form URL is EMPTY [{$order->order_number} | {$order->id}]! Data: " . var_export($responseData, true), 'common\components\kfssalfaapiv1');
                }
            }
        }

        return false;
    }

    public function getOrderPayment($order)
    {
        if (is_numeric($order)) {
            $order = ShopOrder::findOne($order);
        }

        //Работаем через новую модель Заказа
        if(!($order instanceof  ShopOrder)){
            $order = ShopOrder::findOne($order->id);
        }

        if ($order && $order->order_number) {
            $responseData = $this->_call('PUT', 'alfabankorderregister/' . $order->order_number);

            if ($responseData && isset($responseData['errorCode']) && $responseData['errorCode'] == 0) { //ошибок в самом запросе нет
                if (isset($responseData['orderStatus'])) { //Заказ нашелся (есть какой то статус)
                    if ($responseData['orderStatus'] == self::ORDER_PAYMENT_ERRORCODE_SUCCESS) {
                        //Время приходит в микросекундах
                        //Проверить может ли не быть времени платежа
                        $paidDateTs = $responseData['date'] ? $responseData['date'] / 1000 : 0;

                        if ($order->payed != Common::BOOL_Y) {
                            $order->payed = Common::BOOL_Y;
                            $order->payed_at = $paidDateTs;

                            if (!$order->save()) {
                                \Yii::error("ERROR! Can't save order payed [{$order->order_number} | {$order->id}]. Errors: " . var_export($order->getErrors(), true), 'common\components\kfssalfaapiv1');
                            }else{
                                //По обновленной логике чекаут идет сразу после регистрации оплаты и тут он по сути не нужен, будем отталкиваться от статуса
                                if ($order->status_code == ShopOrderStatus::STATUS_WAIT_PAY){
                                    $isKfssApiDisabled = \Yii::$app->kfssApiV2->isDisable;

                                    //Отключаем отключение
                                    if ($isKfssApiDisabled){
                                        \Yii::$app->kfssApiV2->isDisable = false;
                                    }

                                    /** @var Client $responseDataCheckout */
                                    $responseDataCheckout = \Yii::$app->kfssApiV2->checkoutOrder($order);

                                    if ($responseDataCheckout && $responseDataCheckout === true) {
                                        $order->setStatus(ShopOrderStatus::STATUS_SUCCESS);
                                        $order->save();
                                    } else {
                                        \Yii::error("Ошибка подтверждения заказа в КФСС. Заказ №{$order->id} / КФСС №{$order->order_number}.", 'common\components\kfssalfaapiv1');
                                    }

                                    if ($isKfssApiDisabled){
                                        \Yii::$app->kfssApiV2->isDisable = true;
                                    }
                                }
                            }
                        }

                        return $paidDateTs;
                    }else{
                        \Yii::error("Order #[{$order->order_number} | {$order->id}] NOT PAID! Data: " . var_export($responseData,    true), 'common\components\kfssalfaapiv1');
                    }
                }else{
                    \Yii::error("ERROR! Order #[{$order->order_number} | {$order->id}] NOT FOUND! Data: " . var_export($responseData, true), 'common\components\kfssalfaapiv1');
                }
            }
        }
        return false;
    }

    public function getOrderPaymentStatus($orderId)
    {
        $order = ShopOrder::findOne($orderId);
        if ($order && $order->order_number) {
            $responseData = $this->_call('GET', 'alfabankorderinfo/' . $order->order_number);
        }
        return false;
    }

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
            ->addHeaders(['Authorization' => 'Basic ' . base64_encode("{$this->username}:{$this->password}")]);

        if (!empty($params)) {
            $request->setData($params);
        }else{
            //Ибо ругается если нет данных и нет указания что их блина 0
            $request->addHeaders(['content-length' => 0]);
        }

        Common::startTimer("kfssAlfaApiV1::{$method}-{$url}");

        //На случай глюков и недоступности АПИ КФСС
        try {
            $response = $request->send();
        } catch (\Exception $e) {
//            \Yii::error("kfssApiV3::[{$method}]{$url}: " . $e->getMessage(), "kfssapiv3_exception");
            return false;
        }
        $responseData = $response->getData();

        $time = Common::getTimerTime("kfssAlfaApiV1::{$method}-{$url}", false);

        if ($time > self::TIMEOUT){
            \Yii::error("kfssAlfaApiV1::[{$method}]{$url} time = {$time} sec", "kfssalfaapiv1-long-response");
        }

        \Yii::error("[Time={$time}] Request kfssAlfaApiV1 [{$method}] {$url}, data " . print_r($params ?: '<EMPTY>', true) . ", Response [{$response->getStatusCode()}]: " . print_r($responseData?: '<EMPTY>', true), 'common\components\kfssalfaapiv1');

        if ($response->isOk) {
            return $response->getStatusCode() == 204 ? true : $responseData; //Учет кейса когда в ответ нет данных, только статус
        } else {
            \Yii::error("ERROR! Request kfssAlfaApiV1 [{$method}] {$url}, data " . print_r($params ?: '<EMPTY>', true) . ", Response [{$response->getStatusCode()}]: " . print_r($responseData?: '<EMPTY>', true), 'common\components\kfssalfaapiv1');
            return false;
        }
    }

}