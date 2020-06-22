<?php

namespace modules\shopandshow\models\newEntities\shop;

use console\controllers\queues\jobs\Job;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopOrderStatus;
use modules\shopandshow\services\Survey;

class OrderPos extends ShopOrder
{
    public $order_guid;
    public $channel_guid;

    public $promo = [];
    public $goods = [];

    public function addData()
    {
        if (!$this->order_guid) {
            Job::dump('guid empty');

            return false;
        }

        $siteSaleChanel = false;
        switch ($this->channel_guid) {
            case '6A3032E0EF04D151E0538201090A2BC3': $siteSaleChanel = true; break; // САЙТ Shop & Show
            case '5D9CECF18C301919E0538201090A492C': $siteSaleChanel = true; break; // 88003016010
            case '5D9CECF18C291919E0538201090A492C': $siteSaleChanel = true; break; // 88007755665 соц. сети
            break;
        }

        if (!$siteSaleChanel) {
            return true;
        }

        /** @var ShopOrder $shopOrder */
        if (!$shopOrder = Guids::getEntityByGuid($this->order_guid)) {
            Job::dump('Order not found by guid: '.$this->order_guid);

            return false;
        }

        return $this->createOrderPositions($shopOrder);
    }

    protected function createOrderPositions(ShopOrder $order)
    {
        $basket = new \modules\shopandshow\models\shop\ShopBasket();
        $basket::deleteAll(['order_id' => $order->id]);
        unset($basket);
        foreach ($this->goods as $position) {

            if (!$product = Guids::getEntityByGuid($position['LotGuid'])) {
                Job::dump(' lot not found by guid: '.$position['LotGuid']);

                continue;
            }

            if (!$offer = Guids::getEntityByGuid($position['ModificationGuid'])) {
                Job::dump(' offer not found by guid: '.$position['ModificationGuid']);

                // если нет модификации - берем лот
                $offer = $product;
            }
            $basket = new \modules\shopandshow\models\shop\ShopBasket();
            $basket->setAttributes([
                'order_id' => $order->id,
                'product_id' => $offer->id,
                'quantity' => $position['Quantity'] < 1 ? 1 : $position['Quantity'],
                'name' => $offer->name,
                'price' => $position['Price'],
                'currency_code' => 'RUB',
                'site_id' => $order->site_id,
                'discount_price' => $position['OriginalPrice'] - $position['Price'],
                'has_removed' => 0,
                'discount_name' => null, // TODO $position['Discount']['Guid']
                'discount_value' => (string)($position['OriginalPrice'] - $position['Price']),
                'main_product_id' => $product->product ? $product->product->id : $product->id
            ]);
            if (!$basket->save()) {
                Job::dump(print_r($basket->getErrors(), true));

                return false;
            }
        }

        return true;
    }
}