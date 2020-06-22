<?php


namespace common\helpers;


use common\models\ShopOrder;
use common\models\ShopTypePrice;

class Price
{
    public static $priceTypes = [
        'BASE_OLD' => 'Основная цена СТАРАЯ',
        'SHOPANDSHOW' => 'Цена Shop&show',
        'SALE_OLD' => 'Цена "распродажа" СТАРАЯ',
        'TODAY_OLD' => 'Цена "только сегодня" СТАРАЯ',
        'DISCOUNTED_OLD' => 'Цена "со скидкой" СТАРАЯ',
        'BASE' => 'Цена Базовая',
        'DISCOUNTED' => 'Цена "Со скидкой"',
        'TODAY' => 'Цена "Только сегодня"',
        'SALE' => 'Цена "Распродажа"',
        'BUY' => 'Закупочная цена',
        'CATALOG' => 'Цена каталога',
        'CATALOG2' => 'Цена каталога N2',
        'PRIVATE' => 'Цена закрытой распродажи',
        'SITE1' => 'Цена сайта 1',
        'SITE2' => 'Цена сайта 2',
    ];

    public static $priceLabels = [
        10 => 'Обычная цена', //base
        11 => 'В эфире', //discounted
        12 => 'Только сегодня', //cts
        13 => 'В эфире', //sale
        17 => '', //private
        18 => 'Суперпредложение', //site1
        19 => 'Только онлайн', //site2
    ];

    public static function getPriceTypes()
    {
        $query = ShopTypePrice::find()
            ->select([
                'id', 'name'
            ])
            ->orderBy('name')
            ->asArray()
            ->all();

        $return = [];
        foreach ($query as $row) {
            $return[$row['id']] = $row['name'];

        }
        return $return;
//        return self::$priceTypes;

    }

    public static function getPriceLabel($priceTypeId)
    {
        return (\common\models\Product::USE_SITE_PRICES && !empty(self::$priceLabels[$priceTypeId])) ? self::$priceLabels[$priceTypeId] : '';
    }

    public static function getPriceTypeIdByKfssId($kfssPriceTypeId)
    {
        return ShopTypePrice::$kfssPriceTypesMap[$kfssPriceTypeId] ?? false;
    }

    //Тип цены по которой продается товар (связано с источником (каналом) продажи)
    public static function getPriceForSource($product, $source)
    {
        //TODO Учесть вероятность запроса цены не модификации
        $productPrice = 0;

        switch ($source) {
            case ShopOrder::SOURCE_CPA:
                $cpaMainPriceId = ShopTypePrice::PRICE_TYPE_LP1_ID;
//                $cpaMainPriceId = ShopTypePrice::PRICE_TYPE_DISCOUNTED; //Для тестов если нет цены ЛП можно взять эту
                $cpaOtherPriceId = ShopTypePrice::PRICE_TYPE_BASE_ID_OLD;

                //Для лендингов цены берутся по логике: Цена LP1, если пусто то BASE_OLD
                $productPriceMain = 0;
                $productPriceOther = 0;

                $offerPrices = $product->prices;

                if (isset($offerPrices[$cpaMainPriceId]) && $offerPrices[$cpaMainPriceId]->price > 2) {
                    $productPriceMain = $offerPrices[$cpaMainPriceId]->price;
                }

                if (isset($offerPrices[$cpaOtherPriceId]) && $offerPrices[$cpaOtherPriceId]->price > 2) {
                    $productPriceOther = $offerPrices[$cpaOtherPriceId]->price;
                }

                //Если нашлась какая то из цен - имеет смысл выяснять какую используем
                if ($productPriceMain || $productPriceOther){
                    //Если есть обе цены - берем наименьшую
                    if ($productPriceMain && $productPriceOther){
                        $productPrice = min($productPriceMain, $productPriceOther);
                    }else{
                        $productPrice = $productPriceMain ?: $productPriceOther;
                    }
                }

                break;
            case ShopOrder::SOURCE_SITE:
            default:
                $productPrice = $product->new_price;
        }

        return $productPrice;
    }
}