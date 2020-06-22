<?php

namespace modules\shopandshow\components\export;


use common\helpers\Admitad;
use common\interfaces\Export;
use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopOrderStatus;
use skeeks\cms\helpers\FileHelper;

class AdmitadCompareHandler extends YandexHandler implements Export
{

    public $xml = null;

    public function init()
    {
        parent::init();
        $this->name = 'Admitad Compare';


        if (!$this->file_path) {
            $this->file_path = "/export/admitad-compare/feed.xml";
        }
    }


    public function export()
    {
        $this->result->stdout("\tSKIP\n");
        return $this->result;

        //TODO: if console app
        \Yii::$app->urlManager->baseUrl = $this->base_url;
        \Yii::$app->urlManager->scriptUrl = $this->base_url;

        ini_set("memory_limit", "8192M");
        set_time_limit(0);

        //Создание дирректории
        if ($dirName = dirname($this->rootFilePath)) {
            $this->result->stdout("Создание дирректории\n");

            if (!is_dir($dirName) && !FileHelper::createDirectory($dirName)) {
                throw new \Exception("Не удалось создать директорию для файла");
            }
        }

        $imp = new \DOMImplementation();
//        $dtd = $imp->createDocumentType('yml_catalog', '', "shops.dtd");
        $xml = $imp->createDocument('', '');
        $xml->encoding = 'utf-8';
        //$xml->formatOutput = true;

        $yml_catalog = $xml->appendChild(new \DOMElement('Payments'));
        $yml_catalog->appendChild(new \DOMAttr('xmlns', 'http://admitad.com/payments-revision”'));

        $this->result->stdout("\tДобавление основной информации\n");

//        $shop = $yml_catalog->appendChild(new \DOMElement('shop'));


//        $this->_appendOffersArray($yml_catalog);

        foreach ($this->getProductQuery()->each() as $shopProduct) {
            $xoffer = $yml_catalog->appendChild(new \DOMElement('Payment'));
            $xoffer->appendChild(new \DOMElement('OrderID', $shopProduct->id));
            $xoffer->appendChild(new \DOMElement('Status', Admitad::getStatusFromOrder($shopProduct)));

        }


        $xml->formatOutput = true;
        $xml->save($this->rootFilePath);

        return $this->result;
    }

    public function getProductQuery()
    {
        /**
         * SELECT
         * orders.id AS order_id,
         * orders.price,
         * orders.status_code,
         * statuses.name
         * FROM shop_order AS orders
         * LEFT JOIN shop_order_status AS statuses ON orders.status_code = statuses.code
         * WHERE
         * orders.id IN (
         * 227813, 227864, 228153, 229272, 229668, 229754, 229835, 229861, 230152, 230456, 230484, 230659, 230877, 231036, 231075, 231076, 231156, 231358, 231460, 231518, 231544, 231548, 231634,
         * 233053, 233085, 233089, 233166, 233274, 233339, 233368, 233402, 233516, 233799, 233814, 234111, 234268, 234322, 234777, 234858, 235092, 235348, 235440, 235477, 235504, 235639, 235643
         * );
         */

        return ShopOrder::find()
            ->andWhere(['status_code' => [ShopOrderStatus::STATUS_COMPLETED, ShopOrderStatus::STATUS_CANCELED]])
            ->andWhere(['source' => ShopOrder::SOURCE_CPA])
            ->andWhere(['source_detail' => ShopOrder::SOURCE_DETAIL_CPA_ADMITAD])
            ->andWhere(['>', 'created_at', strtotime('-3 months')]);
//            ->andWhere(['<', 'created_at',])
//            ->limit(10000);
    }


    public function getNameForCPC()
    {
        return 'admitad_compare';
    }

    public function getNameType()
    {
        return 'simple';
    }
}