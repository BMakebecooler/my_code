<?php

namespace common\components\sale\channels;

use modules\shopandshow\models\shop\ShopOrder;
use common\components\sale\SaleChannelIntarface;
use common\components\sale\AbstractChannel;
//use modules\shopandshow\models\shop\ShopOrder;
use yii\httpclient\Client;
use yii\db\Exception;
use Yii;

class AdmitadChannel extends AbstractChannel implements saleChannelIntarface
{
    static $TARIFFS = [
        'NEW_CLIENT' => 1,
        'OLD_CLIENT' => 2
    ];
    static $ACTIONS = [
        'ORDER_CONFIRM' => 1
    ];

//    static $KEYS = [
//        'CAMPAIGN_CODE' => 'b77fa81450',
//        'SECRET' => '04A22B59CCAb5Ac836150a0bc2b1a148'
//    ];

//    public $endpoint = 'https://ad.admitad.com/r';


    protected $label = 'admitad_uid';

    private function generateParams($data) {
        $tariff = self::$TARIFFS[ $data['is_first_buy'] ? 'NEW_CLIENT' : 'OLD_CLIENT'];
        return array_merge($data, [
            'postback' => 1,
            'postback_key' => Yii::$app->admitad->secret,
            'campaign_code' => Yii::$app->admitad->campaign_code,
            'action_code' => self::$ACTIONS['ORDER_CONFIRM'],
            'tariff_code' => $tariff,
            'payment_type' => 'sale',
            'currency_code' => 'RUB',
        ]);
    }

    public function trackCheckout(int $orderId, string $label)
    {
        $client = new Client();
        if(!$label) {
            return false;
        }

        try {
            $order = ShopOrder::find()->where(['id' => $orderId])->one();
        }catch (Exception $e){
            throw new Exception('Error create order object ' .$e->getMessage());
        }

        $products = [];
        foreach ($order->shopBaskets as $product) {
            $products[] = $product->toArray();
        }

        try {
            $order->source = ShopOrder::SOURCE_CPA;
            $order->source_detail = ShopOrder::SOURCE_DETAIL_CPA_ADMITAD;
            $order->update();

        }catch (Exception $e) {
            throw new Exception('Error update order object ' .$e->getMessage());
        }

        foreach ($products as $key => $product) {
            /** собираем данные о товарах */
            $_data = [
                'order_id' => $order->id,
                'is_first_buy' => self::isFirstBuy($order->buyer),
                'uid' => $label,

                /** информация о купленном продукте */
                'price' => $product['price'],
                'position_id' => $key+1,
                'position_count' => count($products),
                'quantity' => intval($product['quantity'])
            ];
            $params = $this->generateParams($_data);

            /** @var \yii\httpclient\Response $response */
            try {
                $client->createRequest()
                    ->setMethod('POST')
                    ->setUrl(Yii::$app->admitad->endpoint . '?' . http_build_query($params) )
                    ->send();
            } catch (Exception $e) {
                throw new Exception('Error send data to adminted ' .$e->getMessage());
            }
        }
        return true;

    }

}