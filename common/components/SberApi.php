<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 07.02.19
 * Time: 13:51
 */

namespace common\components;


use common\models\Product;
use modules\shopandshow\models\shop\ShopOrderStatus;
use yii\base\Component;
use yii\di\Instance;
use yii\httpclient\Client;
use modules\shopandshow\models\shop\ShopOrder;

class SberApi extends Component
{
    const TIMEOUT = 20;

    //отправлять ли на оплату список товаров
    public $sendOrderPositions = true;

    public $httpClient;
    public $baseUrl;
    public $username;
    public $password;


    /*
     *
Тип оплаты возможны следующие значения:

1 - полная предварительная оплата до момента передачи предмета расчёта;
2 - частичная предварительная оплата до момента передачи предмета расчёта;
3 - аванс;
4 - полная оплата в момент передачи предмета расчёта;
5 - частичная оплата предмета расчёта в момент его передачи с последующей оплатой в кредит;
6 - передача предмета расчёта без его оплаты в момент его передачи с последующей оплатой в кредит;
7 - оплата предмета расчёта после его передачи с оплатой в кредит.

    */

    public static $paymentMethods = [
        'prepayment_full' => 1,
        'prepayment_partial' => 2,
    ];

    /**
     * Тип оплачиваемой позиции, возможны следующие значения:

    1 - товар;
    2 - подакцизный товар;
    3 - работа;
    4 - услуга;
    5 - ставка азартной игры;
    6 - выигрыш азартной игры;
    7 - лотерейный билет;
    8 - выигрыш лотереи;
    9 - предоставление РИД;
    10 - платёж;
    11 - агентское вознаграждение;
    12 - составной предмет расчёта;
    13 - иной предмет расчёта.
     */

    public static $paymentObjects = [
        'product' => 1,
        'job' => 3,
        'service' => 4,
    ];

    public function init()
    {
        if (is_array($this->httpClient)) {
            $this->httpClient = Instance::ensure($this->httpClient);

            $this->httpClient->baseUrl = $this->baseUrl;
        }
        parent::init();
    }

    public function createPaymentOrder($orderId, $amount, $returnUrl)
    {
        if (!empty($orderId)) {
            $order = ShopOrder::findOne($orderId);
            $orderNumber = $this->getOrderIdForSecondRequest($order);

            $requestData = [
                'userName' => $this->username,
                'password' => $this->password,
                'orderNumber' => $orderNumber,
                'amount' => (int)$amount,
                'returnUrl' => $returnUrl

            ];

            if ($orderPositions = $this->getOrderPositions($order)){

                //* Доставка *//

                //Не находится как можно доставку передать доставкой с указанием суммы
                //Так что пока добавляем как отдельный товар что бы суммы сходились у товаров и самого заказа
                if ($order->price_delivery > 0){
                    $orderPositions[] = [
                        'positionId' => 1,
                        'name' => "Доставка",
                        'quantity' => [
                            'value' => 1,
                            'measure' => 'шт', //$shopBasket->product->unit->name
                        ],
                        'itemCode' => 'delivery',
                        'itemAmount' => (int)$order->price_delivery * 100,
//                'itemCurrency' => (string)643,
                        'itemPrice' => (int)$order->price_delivery * 100,
                        'itemAttributes' => [
                            'attributes' => [
                                [
                                    'name'  => 'paymentMethod',
                                    'value' => self::$paymentMethods['prepayment_full'],
                                ],
                                [
                                    'name' => 'paymentObject',
                                    'value' => self::$paymentObjects['service'],
                                ]
                            ]
                        ]
                    ];
                }

                //* /Доставка *//

                //* Сбор *//

                //Не находится как можно доставку передать доставкой с указанием суммы
                //Так что пока добавляем как отдельный товар что бы суммы сходились у товаров и самого заказа
                if (true){
                    $orderPositions[] = [
                        'positionId' => 2,
                        'name' => "СЕРВИСНЫЙ СБОР",
                        'quantity' => [
                            'value' => 1,
                            'measure' => 'шт', //$shopBasket->product->unit->name
                        ],
                        'itemCode' => 'service_charge',
                        'itemAmount' => 50 * 100,
//                'itemCurrency' => (string)643,
                        'itemPrice' => 50 * 100,
                        'itemAttributes' => [
                            'attributes' => [
                                [
                                    'name'  => 'paymentMethod',
                                    'value' => self::$paymentMethods['prepayment_full'],
                                ],
                                [
                                    'name' => 'paymentObject',
                                    'value' => self::$paymentObjects['service'],
                                ]
                            ]
                        ]
                    ];
                }

                //* /Сбор *//

                $requestData['orderBundle'] = json_encode([
                    'cartItems' => [
                        'items' => $orderPositions,
                    ],
                ]);
            }

            //For tests
            /*
            'items' => [
                [
                    'positionId' => 1,
                    'name' => 'Платье',
                    'quantity' => [
                        'value' => 1,
                        'measure' => 'шт', //$shopBasket->product->unit->name
                    ],
                    'itemCode' => '005222009',
                    'itemAmount' => 99900,
                    //'itemCurrency' => 643,
                    'itemPrice' => 99900
                ],
            ],
            */

            $request = $this->httpClient->createRequest()
                ->setMethod('POST')
                ->setFormat(Client::FORMAT_URLENCODED)
//                ->setUrl('registerPreAuth.do')
                ->setUrl('register.do')
                ->setData($requestData)
                ->setOptions([
                    'timeout' => self::TIMEOUT,
                ]);

            \Yii::error("SberApiSend. RequestData: " . var_export($requestData, true), __METHOD__);

            $response = $request->send();
            $responseData = $response->getData();

            \Yii::error("SberApiSend. ResponseData: " . var_export($responseData, true), __METHOD__);

            $orderPaymentFormUrl = '';

            if (empty($responseData['errorCode'])) {
                $order->order_payment_number = $responseData['orderId'];
                $order->save();
                $orderPaymentFormUrl = $responseData['formUrl'];
            } elseif ($responseData['errorCode'] === '1' && $order->count_payment < 5) {
                $orderPaymentFormUrl = $this->createPaymentOrder($order->id, $amount, $returnUrl);
            }

            if ($orderPaymentFormUrl){
                return $orderPaymentFormUrl;
            }else{
                \Yii::error('Order create fail: ' . print_r($responseData['errorMessage'], true), __METHOD__);
                return false;
            }
        }
        \Yii::error('Order number is empty!', __METHOD__);
        return false;
    }

    /** @var $order \common\models\ShopOrder */
    public function getOrderIdForSecondRequest($order)
    {
        $order_num = (string)$order->order_number . '_' . (string)$order->count_payment;
        $order->count_payment = $order->count_payment + 1;
        $order->save();
        return $order_num;

    }

    public function checkPayStatus($payOrderId)
    {
        $requestData = [
            'userName' => $this->username,
            'password' => $this->password,
            'orderId' => $payOrderId
        ];
        $request = $this->httpClient->createRequest()
            ->setMethod('POST')
            ->setFormat(Client::FORMAT_URLENCODED)
            ->setUrl('getOrderStatus.do')
            ->setData($requestData)
            ->setOptions([
                'timeout' => self::TIMEOUT,
            ]);

        $response = $request->send();
        $responseData = $response->getData();

        return $responseData;
    }

    public function ifPaymentSuccess($orderId)
    {
        $order = ShopOrder::findOne($orderId);
        $payOrderId = $order->order_payment_number;

        \Yii::error("Check order [{$order->id} | {$order->order_number}] payment status for order_payment_number='{$order->order_payment_number}'", __METHOD__);

        if ($payOrderId) {
            $payOrderStatus = $this->checkPayStatus($payOrderId);

            \Yii::error("PaymentResponse order [{$order->id} | {$order->order_number}]: " . var_export($payOrderStatus, true), __METHOD__);

            if (($payOrderStatus['OrderStatus'] === 1 || $payOrderStatus['OrderStatus'] === 2) && $order->payed != 'Y') {
                $order->payed = 'Y';
                $order->payed_at = time();
                $order->save();

                try {
                    $isKfssApiDisabled = \Yii::$app->kfssApiV2->isDisable;

                    //Отключаем отключение
                    if ($isKfssApiDisabled){
                        \Yii::$app->kfssApiV2->isDisable = false;
                    }

                    \Yii::$app->kfssApiV2->payOrder($order->order_number, $payOrderId);
                    /** @var Client $responseData */
                    $responseData = \Yii::$app->kfssApiV2->checkoutOrder($order);

//                    if ($responseData && ($responseData === true || ($responseData->isOk && $responseData->statusCode == 200))) {
                    if ($responseData && $responseData === true) {
                        //Если запрос прошел нормально - выставляем статус что заказ пришел в удаленную систему
                        $order->setStatus(ShopOrderStatus::STATUS_SUCCESS);
                        $order->save();
                    } else {
                        \Yii::error("Ошибка подтверждения заказа в КФСС. Заказ №{$order->id} / КФСС №{$order->order_number}.", 'checkoutLogSberApi');
                    }

                    //Если в целом АПИ отключено то возвращаем в исходное состояние
                    if ($isKfssApiDisabled){
                        \Yii::$app->kfssApiV2->isDisable = true;
                    }
                } catch (\Exception $e) {
                    \Yii::error($e->getMessage(), __METHOD__);
                }

                return true;
            }else if(($payOrderStatus['OrderStatus'] === 1 || $payOrderStatus['OrderStatus'] === 2) && $order->payed == 'Y'){
                return true;
            }
        }

        return false;
    }

    /**
     * @param $order ShopOrder
     */
    public function getOrderPositions($order)
    {
        if (!$this->sendOrderPositions){
            return false;
        }

        $products = [];

        $shopBaskets = $order->shopBaskets;
        $productsPrice = 0;

        foreach ($shopBaskets as $index => $shopBasket) {
            $shopBasketAmount = $shopBasket->price * $shopBasket->quantity;
            $productsPrice += $shopBasketAmount;

            //Для передачи КФСС-идентификатора товара подключаем модель товара
            $product = Product::findOne(['id' => $shopBasket->product_id]);

            $products[] = [
                'positionId' => (string)$shopBasket->id,
                'name' => $shopBasket->name,
                'quantity' => [
                    'value' => (int)$shopBasket->quantity,
                    'measure' => 'шт', //$shopBasket->product->unit->name
                ],
                'itemCode' => (string)(!empty($product->kfss_id) ? $product->kfss_id : $shopBasket->product_id),
                'itemAmount' => (int)$shopBasketAmount * 100, //Сумма стоимости всех товарных позиций одного positionId в деньгах в минимальных единицах валюты
//                'itemCurrency' => (string)643,
                'itemPrice' => (int)$shopBasket->price * 100, //Стоимость одной товарной позиции данного positionId в деньгах в минимальных единицах валюты.
                'itemAttributes' => [
                    'attributes' => [
                        [
                            'name'  => 'paymentMethod',
                            'value' => self::$paymentMethods['prepayment_full'],
                        ],
                        [
                            'name' => 'paymentObject',
                            'value' => self::$paymentObjects['product'],
                        ]
                    ]
                ]
            ];
        }

        $orderPrice = $order->price;

        if ($productsPrice != $orderPrice) {
            \Yii::error("OrderID={$order->id}. Order price!=productsPrice ({$orderPrice}!={$productsPrice})", __METHOD__);
            return false;
        }

        return $products;
    }
}

