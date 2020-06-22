<?php

namespace console\controllers\queues\jobs\shop;

use console\controllers\queues\jobs\Job;
use modules\shopandshow\models\newEntities\shop\Order as OrderModel;
use modules\shopandshow\models\shop\ShopOrder;

class Order extends Job
{
    /**
     * @param \yii\queue\Queue $queue
     * @param string $guid
     *
     * @return bool
     * @throws \Exception
     */
    public function execute($queue, &$guid)
    {
        if ($this->prepareData($queue)) {
            $guid = $this->data['Data']['OrderGuid'];

            return $this->addOrder();
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function addOrder()
    {
        $info = $this->data['Info'];
        $data = $this->data['Data'];

        if ($data['BPGuid'] != '5E7BA91651501219E0538201090ACBAD') {
            return true;
        }

        $siteSaleChanel = false;
        switch ($data['ChannelGuid']) {
            case  '81C98E92CC656F0CE0538201090A37B4':
                $siteSaleChanel = true;
                $siteOrderSource = ShopOrder::SOURCE_CPA;
                $siteOrderSourceDetail = ShopOrder::SOURCE_DETAIL_CPA_KMA;
                break; // CPA
            case '6A3032E0EF04D151E0538201090A2BC3':
                $siteSaleChanel = true;
                $siteOrderSource = ShopOrder::SOURCE_SITE;
                $siteOrderSourceDetail = ShopOrder::SOURCE_DETAIL_SITE;
                break; // САЙТ Shop & Show
            case '5D9CECF18C301919E0538201090A492C':
                $siteSaleChanel = true;
                $siteOrderSource = ShopOrder::SOURCE_KFSS;
                $siteOrderSourceDetail = ShopOrder::SOURCE_DETAIL_PHONE2;
                break; // 88003016010
            case '5D9CECF18C291919E0538201090A492C':
                $siteSaleChanel = true;
                $siteOrderSource = ShopOrder::SOURCE_KFSS;
                $siteOrderSourceDetail = ShopOrder::SOURCE_DETAIL_PHONE1;
                break; // 88007755665 соц. сети
                break;
        }

        if (!$siteSaleChanel) {
            Job::dump('SkipNotSiteSaleChannel');
            return true;
        }

        Job::dump('----- Order -------');
        Job::dump('OrderGuid: '.$data['OrderGuid']);
        Job::dump('OrderNumber: '.$data['OrderNumber']);
        Job::dump('PhoneMain: '.$data['PhoneMain']);

        if (!$data['PhoneMain']) {
            Job::dump('PhoneMainIsEmpty!');
            \Yii::error('EmptyPhone: ' . var_export($data, true), 'queue_order_phone_empty');
            return false;
        }

        $order = new OrderModel();

        $order->info_source = $info['Source'];

        $order->order_guid = $data['OrderGuid'];
        $order->order_number = $data['OrderNumberKFSS'];
        $order->order_createdate = $data['CreateDate'];
        $order->order_comment = $data['Comment'];
        $order->order_price = $data['Sum'];
        $order->order_original_price = $data['OriginalSum'];
        $order->order_source = $siteOrderSource;
        $order->order_source_detail = $siteOrderSourceDetail;

        $order->order_delivery = $data['Delivery']; // Array
        $order->order_discount = $data['Discount']; // Array

        $order->client_guid = $data['ClientGuid'];
        $order->client_name = $data['ClientName'];
        $order->client_email = $data['ClientEmail'];
        $order->client_bitrix_id = $data['ClientID'];
        $order->client_phone = $data['PhoneMain']; // PhoneCid
        $order->client_ext_phone = $data['PhoneExt'];

        $order->client_address = $data['Address']; // Array

        return $order->addData();
    }
}