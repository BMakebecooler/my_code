<?php

namespace common\models;

use common\helpers\ArrayHelper;
use modules\shopandshow\models\common\GuidBehavior;
use Yii;

class ShopTypePrice extends \common\models\generated\models\ShopTypePrice
{
    const PRICE_TYPE_SS_ID = 6;
    const PRICE_TYPE_BASE_ID_OLD = 10; //Базовая цена оригинальная
    const PRICE_TYPE_DISCOUNTED = 11; //
    const PRICE_TYPE_TODAY = 12; //CTS
    const PRICE_TYPE_SALE = 13; //
    const PRICE_TYPE_BUY = 14; //
    const PRICE_TYPE_CATALOG = 15; //
    const PRICE_TYPE_CATALOG2 = 16; //
    const PRICE_TYPE_PRIVATE = 17; //
    const PRICE_TYPE_SITE2_ID = 18; //Цена сайта 2
    const PRICE_TYPE_SITE1_ID = 19; //Цена сайта 1
    const PRICE_TYPE_SITE3_ID = 20; //Цена сайта 3
    const PRICE_TYPE_LP1_ID = 25; //Цена
    const PRICE_TYPE_LP2_ID = 26; //Цена

    const LOT_NUM_ID = 102;

    public static $priceTypes = [
        10 => ['id' => 10, 'name' => 'Базовая', 'guid' => '563E116BD311D870E0534301090AFABA'], //Старая Базовая
        11 => ['id' => 11, 'name' => 'Цена Со скидкой', 'guid' => '563E116BD313D870E0534301090AFABA'], //Цена Со скидкой
        12 => ['id' => 12, 'name' => 'Цена Только сегодня', 'guid' => '563E116BD315D870E0534301090AFABA'], //Цена Только сегодня
        13 => ['id' => 13, 'name' => 'Цена Распродажа', 'guid' => '563E116BD31BD870E0534301090AFABA'], //Цена Распродажа
        14 => ['id' => 14, 'name' => 'Закупочная цена', 'guid' => '563E116BD321D870E0534301090AFABA'], //Закупочная цена
        15 => ['id' => 15, 'name' => 'Цена каталога', 'guid' => '563E116BD31FD870E0534301090AFABA'], //Цена каталога
        16 => ['id' => 16, 'name' => 'Цена каталога №2', 'guid' => '563E116BD328D870E0534301090AFABA'], //Цена каталога №2
        17 => ['id' => 17, 'name' => 'Цена закрытой распродажи', 'guid' => '563E116BD306D870E0534301090AFABA'], //Цена закрытой распродажи
        18 => ['id' => 18, 'name' => 'Цена Сайта2', 'guid' => '91CF008A53BC5989E0534401090AA006'], //Цена Сайта2
        19 => ['id' => 19, 'name' => 'Цена Сайта1', 'guid' => '90389B6144973B30E0534401090A7461'], //Цена Сайта1
        20 => ['id' => 20, 'name' => 'Цена Сайта3', 'guid' => '9C16C35C8B63FAF2E0538201090AC267'], //Цена Сайта3
        25 => ['id' => 25, 'name' => 'Цена LP1', 'guid' => '9BC8FD7E5A890B44E0538201090AA5D2'], //Цена для Лендингов1
        26 => ['id' => 26, 'name' => 'Цена LP2', 'guid' => '9BC8FD7E5A8A0B44E0538201090AA5D2'], //Цена для Лендингов2
        30 => ['id' => 30, 'name' => 'Цена для ШикТВ', 'guid' => '5E9455C04DB20C75E0538201090A256E'], //Цена для ШикТВ
    ];

    public static $priceTypesByGuid = [
        '563E116BD311D870E0534301090AFABA' => 10, //Старая Базовая
        '563E116BD313D870E0534301090AFABA' => 11, //Цена Со скидкой
        '563E116BD315D870E0534301090AFABA' => 12, //Цена Только сегодня
        '563E116BD31BD870E0534301090AFABA' => 13, //Цена Распродажа
        '563E116BD321D870E0534301090AFABA' => 14, //Закупочная цена
        '563E116BD31FD870E0534301090AFABA' => 15, //Цена каталога
        '563E116BD328D870E0534301090AFABA' => 16, //Цена каталога №2
        '563E116BD306D870E0534301090AFABA' => 17, //Цена закрытой распродажи
        '91CF008A53BC5989E0534401090AA006' => 18, //Цена Сайта2
        '90389B6144973B30E0534401090A7461' => 19, //Цена Сайта1
        '9C16C35C8B63FAF2E0538201090AC267' => 20, //Цена Сайта3
        '9BC8FD7E5A890B44E0538201090AA5D2' => 25, //Цена LP1
        '9BC8FD7E5A8A0B44E0538201090AA5D2' => 26, //Цена LP2
        '5E9455C04DB20C75E0538201090A256E' => 30, //Цена для ШикТВ
    ];

    //Типы цен номиналы которых мы сохраняем в списке типов цен товаров
    public static $savePriceTypes = [
        10, 18, 19, 20, 25, 26
    ];

    public static $kfssPriceTypesMap = [
        2570 => self::PRICE_TYPE_BASE_ID_OLD,
        2610 => self::PRICE_TYPE_DISCOUNTED,
        2650 => self::PRICE_TYPE_TODAY,
        2770 => self::PRICE_TYPE_SALE,
        2890 => self::PRICE_TYPE_BUY,
        4210 => self::PRICE_TYPE_SITE1_ID,
        4220 => self::PRICE_TYPE_SITE2_ID,
    ];

    const TYPE_BASE = 'BASE';
    const TYPE_DISCOUNT = 'DISCOUNT';
    const TYPE_SHOPANDSHOW = 'SHOPANDSHOW';
    const TYPE_SALE = 'SALE';
    const TYPE_TODAY = 'TODAY';
    const TYPE_DISCOUNTED = 'DISCOUNTED';


    const NAME_BASE = 'Основная цена';
    const NAME_DISCOUNT = 'Цена со скидкой';
    const NAME_SHOPANDSHOW = 'Цена Shop&show';
    const NAME_SALE = 'Цена "распродажи';
    const NAME_TODAY = 'Цена "только сегодня';
    const NAME_DISCOUNTED = 'Цена премьеры';

    /**
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_BASE => self::NAME_BASE,
            self::TYPE_DISCOUNT => self::NAME_DISCOUNT,
            self::TYPE_SHOPANDSHOW => self::NAME_SHOPANDSHOW,
            self::TYPE_SALE => self::NAME_SALE,
            self::TYPE_TODAY => self::NAME_TODAY,
            self::TYPE_DISCOUNTED => self::NAME_DISCOUNTED,
        ];
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            GuidBehavior::className() => GuidBehavior::className()
        ]);
    }

    //Получить основные данные типа цены по ГУИДу
    public static function getPriceTypeMainDataByGuid ($guid, $addIfNotExist = false)
    {
        $priceType = false;

        //Если тип цены не определен - то продолжать смысла нет
        if (empty($guid)){
            return false;
        }

        if (isset(self::$priceTypesByGuid[$guid])){
            $priceTypeId = self::$priceTypesByGuid[$guid];
            $priceType = self::$priceTypes[$priceTypeId] ?? false;
        }else{
            //Какой то новый тип цены, пробуем найти его в БД
            $shopTypePrice = self::find()->innerJoin('ss_guids', 'shop_type_price.guid_id=ss_guids.id')->andWhere(['guid' => $guid])->one();

            if ($shopTypePrice){
                $priceType = ['id' => $shopTypePrice->id, 'name' => $shopTypePrice->name];
            }elseif ($addIfNotExist){
                $shopTypePrice = new self();

                $shopTypePrice->guid->setGuid($guid);
                $shopTypePrice->code = uniqid();
                $shopTypePrice->name = uniqid();

                if (!$shopTypePrice->save()) {
                    \Yii::error($shopTypePrice->getErrors(), __METHOD__);
                    return false;
                }

                $priceType = ['id' => $shopTypePrice->id, 'name' => $shopTypePrice->name];
            }
        }

        return $priceType;
    }

    //Получить основные данные типа цены по ID
    public static function getPriceTypeMainDataById ($id)
    {
        $priceType = false;

        //Если тип цены не определен - то продолжать смысла нет
        if (empty($id)){
            return false;
        }

        if (isset(self::$priceTypes[$id])){
            $priceType = self::$priceTypes[$id] ?? false;
        }else{
            //Какой то новый тип цены, пробуем найти его в БД
            $shopTypePrice = self::find()->innerJoin('ss_guids', 'shop_type_price.guid_id=ss_guids.id')->andWhere(['id' => $id])->one();

            if ($shopTypePrice){
                $priceType = ['id' => $shopTypePrice->id, 'name' => $shopTypePrice->name];
            }
        }

        return $priceType;
    }
}
