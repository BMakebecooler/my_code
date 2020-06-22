<?php

namespace modules\shopandshow\models\shop;

use modules\shopandshow\lists\traits\SsContentElementsWidgets;
use modules\shopandshow\models\mediaplan\AirDayProductTime;
use modules\shopandshow\models\statistic\ShopProductStatistic;
use modules\shopandshow\models\traits\SsContentElement;
use skeeks\cms\components\Cms;
use skeeks\cms\shop\models\ShopCmsContentElement;

/**
 * @property int $bitrix_id
 * @property bool isStockSegment
 *
 * @property ShopFuserFavorite $favorite
 * @property SsShopProductPrice $price
 * @property AirDayProductTime $mediaPlanScheduleItem
 * @property ShopProductStatistic $productStatistic
 */
class ShopContentElement extends ShopCmsContentElement
{
    use SsContentElement;
    use SsContentElementsWidgets;


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProduct()
    {
        return $this->hasOne(ShopProduct::className(), ['id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMediaPlanScheduleItem()
    {
        return $this->hasOne(AirDayProductTime::className(), ['lot_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductStatistic()
    {
        return $this->hasOne(ShopProductStatistic::className(), ['id' => 'id']);
    }


    public function getCardIds()
    {
        $array = [];
        $lot = $this;
        if ($lot->parent_content_element_id) {
            $lot = $lot->parentContentElement;
        }
        if ($lot->parent_content_element_id) {
            $lot = $lot->parentContentElement;
        }
        foreach ($lot->childrenContentElements as $childrenContentElement) {
            if ($childrenContentElement->active == Cms::BOOL_Y) {
                $array[$childrenContentElement->id] = $childrenContentElement->id;
            }
        }

        return $array;
    }

    /**
     * Получить список модификаций для Ритейл рокета
     * @return array
     */
    public function getOffersForRr()
    {
        $rrOffers = [];

        $offersParams = \common\widgets\products\ModificationsWidget::getInstance()->getOffersParameters();

        if ($offersParams) {

            $offers = $this->childrenContentElements;

            $paramsListSize = [
                'p_SIZE_name',
                'p_SIZE_BATTERY_name',
                'p_SIZE_BRASSIERE_name',
                'p_SIZE_PANTS_name',
                'p_SIZE_SHOES_name',
                'p_SIZE_CLOTHES_name',
                'p_SIZE_RINGS_name'
            ];
            $paramsListColor = ['p_COLOR_name', 'p_COLOR_REF_name'];

            foreach ($offersParams as $k => $offerParams) {
                $offerId = $offerParams['offer_id'];

                $offer = array_filter($offers, function ($offer) use ($offerId) {
                    return $offer->id == $offerId;
                });

                $offer = $offer ? current($offer) : false;

                $attrSize = array_filter($offerParams, function ($propVal, $propName) use ($paramsListSize) {
                    return !empty($propVal) && in_array($propName, $paramsListSize);
                }, ARRAY_FILTER_USE_BOTH);

                $attrColor = array_filter($offerParams, function ($propVal, $propName) use ($paramsListColor) {
                    return !empty($propVal) && in_array($propName, $paramsListColor);
                }, ARRAY_FILTER_USE_BOTH);


                $rrOffers[$offerId]['isAvailable'] = !empty($offer->active) && $offer->active == Cms::BOOL_Y ? true : false;

                /*if(!empty($offer->name)){
                    $rrOffers[$offerId]['name'] =  $offer->name;
                }*/
                if ($attrSize) {
                    $rrOffers[$offerId]['size'] = current($attrSize);
                }
                if ($attrColor) {
                    $rrOffers[$offerId]['color'] = current($attrColor);
                }
                if (!empty($offerParams['price'])) {
                    $rrOffers[$offerId]['price'] = (int)$offerParams['price'];
                }
                if (!empty($offerParams['max_price'])) {
                    $rrOffers[$offerId]['oldPrice'] = (int)$offerParams['max_price'];
                }
            }
        }

        return $rrOffers;
    }

    /**
     * Вызвращает массив-маппинг связку лотов-имиджевых изображени
     * @return array
     */
    public static function getFashionImages()
    {

        //product_id => cms_storage_file.id
        return [
            //220537 => 132643, //dev test
            //232926 => 23661, //dev test

            928230 => 3684282,
            1001046 => 3739250,
            980984 => 3774959,
            1007889 => 3805252,
            980395 => 3828692,
            987949 => 3899727,
            885912 => 3930971,
            220537 => 3963988,
            940508 => 4019631,
            232971 => 4052099,
            920476 => 4105851,
            925482 => 4109721,
            1027962 => 4145276,
            982821 => 4178133,
            925005 => 4197277,
            793744 => 4231669,
            1014488 => 4299310,
            222125 => 4321140,
            1039657 => 4353953,
            1028408 => 4394953,
            220157 => 4445750,
            1041383 => 4476847,
            828947 => 4505257,
            915784 => 4516309,
            1012999 => 4567031,
            1017956 => 4606123,
            1028114 => 4664191,
            982281 => 4687479,
            741438 => 4696602,
            979822 => 4716120,
            952098 => 4739062,
            999942 => 4785688,
            232926 => 4796036,
            1045640 => 4815021,
            1059710 => 4838497,
            1059567 => 4870705,
            1034517 => 4882737,
            1052231 => 4894433,
            1049003 => 4905698,
            1065387 => 4934224,
            226979 => 4942490,
            1051403 => 4965527,
            1028075 => 4987736,
            917502 => 4987737,
            1048998 => 5025781,
            1056606 => 5059804,
            1071482 => 5069280,
            1065289 => 5100779,
            761161 => 5118089,
            949781 => 5136354,
            807317 => 5162586,

            781614 => 5316760,
            1061019 => 5254692,
            976310 => 4707718,
            987497 => 5254027,
            1071616 => 5191509,
            236513 => 5235853,
            960607 => 5242364,

            //Доплнения после 2018-08-23
//            781614 => 5316760, //дубль
//            1065289 => 5100779, //дубль
            1071480 => 5127045,
            1085409 => 5390769,
//            925482 => 4109721, //дубль
            1073722 => 5415046,
//            1034517 => 4882915, //дубль

            1073721 => 5556956,
            1081259 => 5556957,
            1098517 => 5556958,
            1073186 => 5556959,
            1075760 => 5556960,
            920796 => 5556961,
            1079763 => 5556962,
            1082251 => 5557164,
            235607 => 5557165,
            1014193 => 5557166,
            1045875 => 5557167,
            1039066 => 5557168,
            //1045875 => 5557167, //дубль двумя строками выше
            1069241 => 5557169,
            1043294 => 5170345,
            1074569 => 5557170,
            930629 => 5557171,
            1080792 => 5557172,
            1066671 => 5557173,

            //1073721 => 5556956, //дубль
            224386 => 5571285,
            1091672 => 5584257,

//            928230 => 5008779, //дубль
//            925005 => 4197277, //дубль
//            925482 => 4109721, //дубль
//            1071480 => 5127045, //дубль

            929002 => 5606436,
            944427 => 5621299,

            1084719 => 5643567,
            1079734 => 5626265,
            1046704 => 5634913,

            781263 => 5652147,
            836715 => 5652148,
            836717 => 5652149,
            836718 => 5652150,
            836720 => 5652151,
            836721 => 5652152,
            836733 => 5652153,
            981338 => 5652154,
//            1059567 => 5652155, //дубль
            1084794 => 5652156,
            1084946 => 5652157,
            1084947 => 5652158,
            1087696 => 5655016,

            1100603 => 5675090,
//            1087696 => 5655016, //дубль
            995044 => 5681439,
            1096649 => 5681440,
            1096650 => 5681441,
            1096651 => 5681442,
            1096652 => 5681443,

            1084416 => 5684165,
//            1052231 => 4894433, //дубль

            821505 => 5713107,
            1087081 => 5703340,
//            1052231 => 4894433, //дубль

            1101523 => 5720924,
            1075762 => 5732319,

            1100604 => 5746668,
            851507 => 5777325,
            936215 => 5777326,
            1012940 => 5777327,
            1062188 => 5777329,
//            1071153 => wait...,
            1109497 => 5777330,
        ];
    }

    /**
     * Возвращает ID имиджевого изображения (если есть) для данного товара
     * @return int|mixed
     */
    public function getFashionImageId()
    {
        $fashionImages = self::getFashionImages();
        return isset($fashionImages[$this->id]) ? $fashionImages[$this->id] : 0;
    }

    /**
     * Возвращает имиджевое изображение товара (если находит)
     * @return array|\skeeks\cms\models\CmsStorageFile
     */
    public function getFashionImage()
    {

        if ($fashionImageId = $this->fashionImageId) {
            foreach ($this->images as $image) {
                if ($image->id == $fashionImageId) {
                    $fashionImage = $image;
                    break;
                }
            }
        }
        return $fashionImage ?? [];
    }

}