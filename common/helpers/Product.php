<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 06/02/2019
 * Time: 14:32
 */

namespace common\helpers;


use common\models\BUFECommFlashPrice;
use common\models\cmsContent\CmsContentElement;
use common\models\CmsContentElementProperty;
use common\models\NewProduct;
use common\models\Product as ProductModel;
use common\models\ProductAbc;
use common\models\query\CmsContentElementQuery;
use common\models\query\SsMediaplanAirDayProductTimeQuery;
use common\models\Setting;
use common\models\ShopTypePrice;
use common\models\SsMediaplanAirDayProductTime;
use common\models\SsShare;
use common\thumbnails\Thumbnail;
use console\jobs\UpdatePriceCardJob;
use console\jobs\UpdatePriceJob;
use console\jobs\UpdatePriceLotJob;
use console\jobs\UpdateQuantityJob;
use modules\shopandshow\models\common\StorageFile;
use modules\shopandshow\models\shop\SsShopProductPrice;
use skeeks\cms\shop\models\ShopProductPrice;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception;

class Product
{
    const SS_PRICE_ID = 6;
    const BASE_PRICE_ID_OLD = 10; //Базовая цена оригинальная
    const BASE_PRICE_ID = 19; //Цена сайта 1
    const LOT_NUM_ID = 102;

    /**
     * @param $id int Modification ID
     * @return bool
     * @throws Exception
     * @throws \yii\base\Exception
     */
    public static function updatePrice($id)
    {
//        $modification = NewProduct::findOne($id);
        $modification = NewProduct::getDb()->cache(function ($db) use ($id) {
            return NewProduct::findOne($id);
        }, HOUR_1);
        $lot = NewProduct::getLot($id);
        self::recalculateModification($modification, $lot);


//        foreach ($lot->childrenContentElements as $card) {
//            foreach ($card->childrenContentElements as $modification) {
//                self::recalculateModification($modification, $lot);
//            }
//            self::recalculate($card);
//        }
//        self::recalculate($lot);
//
//        return true;
    }

    /**
     * Пересчет цен модификации
     * @param $model CmsContentElement
     * @param $lot CmsContentElement
     */
    public static function recalculateModificationOld($model, $lot)
    {

        // todo If clear all shop_product_price and recreate give error duplicate Duplicate entry '1699997-6' for key 'unique_product_priceType'
        /** @var NewProduct $lot */
        if (!$model->isOffer()) {
            //\Yii::error("Error. Not Offer given!", 'common\helpers\Product::recalculateModification()');
            throw new Exception("Error. Not Offer given! // [{$model->id} | {$model->content_id}] {$model->name}");
        }

        //В консоле инфа по пересчету может быть полезной
        if (\common\helpers\App::isConsoleApplication()) {
            echo "Recalc price for offer [{$model->id}] {$model->name} // lotId = '{$lot->id}'" . PHP_EOL;
        }

        //Если цена базовая - пишем ее во все поля ss_shop_product_prices, цену ШШ пищшем равной базовой
        //Если цена не базовая - предварительно необходимо пересчитать цену ШШ (6 тип)

        //ss_shop_product_prices.ss_max_price - всегда цена ШШ
        //ss_shop_product_prices.price - берем из списка цен сущности соответствующую текущему типу цены

        // todo if not exist PRICE_ACTIVE, set from lot
//        $curPriceTypeId = $model->getRelatedPropertiesModel()->getAttribute('PRICE_ACTIVE');
        /** @var NewProduct $model */
        $curPriceTypeId = $model->getPropertyActivePriceId();
//        if (empty($curPriceTypeId)) {
//            $curPriceTypeId = $lot->getRelatedPropertiesModel()->getAttribute('PRICE_ACTIVE');
//        }
        if ($curPriceTypeId) {

            if (\common\helpers\App::isConsoleApplication()) {
                echo "PRICE_ACTIVE prop = {$curPriceTypeId}" . PHP_EOL;
            }

            //Пересчитываем Цену ШШ только если испольуем общие цены, а не механику Цен сайта
            if ($curPriceTypeId != self::BASE_PRICE_ID && !ProductModel::USE_SITE_PRICES) {
                self::recalculatePriceSs($model);
            }

            //Выбираем методом что бы не взять цены из кеша
            $prices = $model->shopProductPrices;
            $prices = \common\helpers\ArrayHelper::index($prices, 'type_price_id');

            $ssShopProductPrice = $model->price ?? new SsShopProductPrice(['product_id' => $model->id, 'type_price_id' => $curPriceTypeId]);

            $ssShopProductPrice->type_price_id = $curPriceTypeId;
            //Если в спиcке цен есть требуемого нам типа - пишем

            //* Страховка от случая когда текущая цена подменилась на ЦенуСайта1, а ее номинала нет *//
            if ($curPriceTypeId == self::BASE_PRICE_ID && empty($prices[self::BASE_PRICE_ID]) && !empty($prices[self::BASE_PRICE_ID_OLD])) {
                echo "New PriceBase Empty - Fill it as PriceBaseOld" . PHP_EOL;
                $prices[self::BASE_PRICE_ID] = $prices[self::BASE_PRICE_ID_OLD];
            }
            //* /Страховка от случая когда текущая цена подменилась на ЦенуСайта1, а ее номинала нет *//

            if (!empty($prices[$curPriceTypeId])) {
                $curPriceTypeModel = $prices[$curPriceTypeId];

                /** @var ShopProductPrice $curPriceTypeModel */
                $ssShopProductPrice->price = $curPriceTypeModel->price;
                $ssShopProductPrice->min_price = $curPriceTypeModel->price;

                /** @var ShopProductPrice $priceBaseModel */

                //Для товаров в эфире в качестве базовой испольуем оригинальную (старую) базовую цену
                $isProductOnAir = SsMediaplanAirDayProductTime::isProductOnAir($lot);
                if ($isProductOnAir) {
                    if (!empty($prices[self::BASE_PRICE_ID_OLD])) {
                        $priceBaseModel = $prices[self::BASE_PRICE_ID_OLD];
                    } else {
                        throw new \yii\base\Exception("ErrorEmptyBasePriceOld. Эфирный товар. Нет старой базовой цены!");
                    }
                } else {
                    if (!empty($prices[self::BASE_PRICE_ID])) {
                        $priceBaseModel = $prices[self::BASE_PRICE_ID];
                    } elseif (!empty($prices[self::BASE_PRICE_ID_OLD])) {
                        $priceBaseModel = $prices[self::BASE_PRICE_ID_OLD];
                    } else {
                        throw new \yii\base\Exception("ErrorEmptyBasePriceAll. Нет ни старой ни новой базовой цены!");
                    }
                }

                //для max_price всегда используем цену шш которая пересчитывается в зависимости от текущего типа цены и должна быть актуальна
                //Если не актуальна, то проблемы изначально от базовой цены
                /** @var ShopProductPrice $priceSsModel */
                $priceSsModel = $prices[self::SS_PRICE_ID] ?? null;

                //Бывает что при текущей базовой цене цена ШШ еще не расчитывалась и/или == 0, учитываем кейс
                if (!$priceSsModel) {
                    $priceSsModel = new ShopProductPrice([
                        'product_id' => $model->id,
                        'type_price_id' => self::SS_PRICE_ID,
                        'price' => $priceBaseModel->price
                    ]);
                    $priceSsModel->save();
                } elseif (!$priceSsModel->price) {
                    $priceSsModel->price = $priceBaseModel->price;
                    $priceSsModel->save();
                }

                //Если используем функционал Сайтовых цен - то "старая цена" это всегда базовая цена
                if (ProductModel::USE_SITE_PRICES) {
                    echo "USE PRICE BASE AS MAX" . PHP_EOL;
                    $ssShopProductPrice->max_price = max(0, $priceBaseModel->price);
                } else {
                    echo "USE PRICE SS AS MAX" . PHP_EOL;
                    $ssShopProductPrice->max_price = max(0, $priceSsModel->price);
                }

                //Если с шш какие то проблемы - ставим текущую цену и скидка будет = 0 если что
                if (!$ssShopProductPrice->max_price) {
                    $ssShopProductPrice->max_price = $ssShopProductPrice->price;
                }

                $ssShopProductPrice->discount_percent = 0;
                if ($ssShopProductPrice->price > 0 && $ssShopProductPrice->max_price > 0) {
                    $ssShopProductPrice->discount_percent = max(0, round((($ssShopProductPrice->max_price - $ssShopProductPrice->price) / $ssShopProductPrice->max_price) * 100));
                }

                if ($ssShopProductPrice->isAttributeChanged('price')
                    || $ssShopProductPrice->isAttributeChanged('max_price')) {
                    if (!$ssShopProductPrice->save()) {
                        if (\common\helpers\App::isConsoleApplication()) {
                            echo "Error save SsShopProductPrices. Errors: " . var_export($ssShopProductPrice->getErrors(), true) . PHP_EOL;
                        }

                        \Yii::error("Error while save mod prices recalculate. Err: " . var_export($ssShopProductPrice->getErrors(), true), 'common\helpers\Product::recalculateModification()');
                        throw new \yii\base\Exception("Error while save mod prices recalculate. Error: " . var_export($ssShopProductPrice->getErrors(), true));
                    } else {

                        //* cms_content_element.ATTRs save *//

                        $model->new_price = $ssShopProductPrice->price;
                        $model->new_price_old = $ssShopProductPrice->type_price_id == $priceBaseModel->id ? $ssShopProductPrice->price : $ssShopProductPrice->max_price;
                        $model->new_price_active = $ssShopProductPrice->type_price_id;
                        $model->new_discount_percent = max(0, $ssShopProductPrice->discount_percent);

                        if (!$model->save()) {
                            \Yii::error("Error while save mod prices recalculate [cms_content_element]. Err: " . var_export($model->getErrors(), true), __METHOD__);
                            throw new \yii\base\Exception("Error while save mod prices recalculate [cms_content_element]. Error: " . var_export($model->getErrors(), true));
                        }

                        //* /cms_content_element.ATTRs save *//

                        if (\common\helpers\App::isConsoleApplication()) {
                            echo "Saved." . PHP_EOL;
                        }
                    }
                } else {
                    if (\common\helpers\App::isConsoleApplication()) {
                        echo "SsShopProductPrices not changed. Skip save." . PHP_EOL;
                    }
                }

                Yii::$app->queue->push(new UpdatePriceCardJob([
                    'id' => $model->parentContentElement->id,
                ]));

                return true;

            } else {
                //У товара нет типа цены значения которого нужны для обновления
                if (\common\helpers\App::isConsoleApplication()) {
                    echo "Error! Required element price type no found! [type='{$curPriceTypeId}']" . PHP_EOL;
                }

                \Yii::error("Error. Has no required price type [{$curPriceTypeId}] data for productID " . $model->id, 'common\helpers\Product::recalculateModification()');
//                throw new \yii\base\Exception("Error. Has no required price type data " . $model->id);
            }

        } else {
            //Отсутствие типа цены вполне рядовая ситуация, не будем забивать этим логи
//            \Yii::error("Error. Has no PRICE_ACTIVE property, id " . $model->id, 'common\helpers\Product::recalculateModification()');
//            throw new Exception("Error. Has no PRICE_ACTIVE property, id " . $model->id);

            if (\common\helpers\App::isConsoleApplication()) {
                echo "Error! No PRICE_ACTIVE prop!" . PHP_EOL;
            }
        }


        return false;
    }

    /**
     * Пересчет цен модификации
     * @param $model CmsContentElement
     * @param $lot CmsContentElement
     */
    //Пересчет без учета указания типа цены
    public static function recalculateModification($model, $lot)
    {
        //return self::recalculateModificationOld($model, $lot);

        // todo If clear all shop_product_price and recreate give error duplicate Duplicate entry '1699997-6' for key 'unique_product_priceType'
        /** @var NewProduct $lot */
        if (!$model->isOffer()) {
            //\Yii::error("Error. Not Offer given!", 'common\helpers\Product::recalculateModification()');
            throw new Exception("Error. Not Offer given! // [{$model->id} | {$model->content_id}] {$model->name}");
        }

        //В консоле инфа по пересчету может быть полезной
        if (\common\helpers\App::isConsoleApplication()) {
            echo "Recalc price for offer [{$model->id}] {$model->name} // lotId = '{$lot->id}'" . PHP_EOL;
        }

        $curPriceTypeId = $model->new_price_active;

        //Текущий тип цены уже не используем
        if (true || $curPriceTypeId) {

            if (\common\helpers\App::isConsoleApplication()) {
                echo "PRICE_ACTIVE prop = {$curPriceTypeId}" . PHP_EOL;
            }

            //Выбираем методом что бы не взять цены из кеша
            $prices = $model->shopProductPrices;
            $prices = \common\helpers\ArrayHelper::index($prices, 'type_price_id');

            //Нас интересуют только Цена сайта 1 и 2, ищем их
            $priceSite1Model = null;
            if (!empty($prices[ShopTypePrice::PRICE_TYPE_SITE1_ID])) {
                $priceSite1Model = $prices[ShopTypePrice::PRICE_TYPE_SITE1_ID];
            }

            $priceSite2Model = null;
            if (!empty($prices[ShopTypePrice::PRICE_TYPE_SITE2_ID])) {
                $priceSite2Model = $prices[ShopTypePrice::PRICE_TYPE_SITE2_ID];
            }

            if ($priceSite1Model) {
                /** @var ShopTypePrice $priceSite1Model */
                /** @var ShopTypePrice $priceSite2Model */
                if (!empty($priceSite2Model->price) && !empty($priceSite1Model->price) && $priceSite2Model->price < $priceSite1Model->price) {
                    $model->new_price = $priceSite2Model->price;
                    $model->new_price_old = $priceSite1Model->price;
                } else {
                    $model->new_price = $priceSite1Model->price;
                    $model->new_price_old = $priceSite1Model->price;
                }

                $model->new_discount_percent = 0;
                if ($model->new_price > 0 && $model->new_price_old > 0 && $model->new_price < $model->new_price_old) {
                    $model->new_discount_percent = max(0, round((($model->new_price_old - $model->new_price) / $model->new_price_old) * 100));
                }

                if ($model->isAttributeChanged('new_price') || $model->isAttributeChanged('new_price_old')) {
                    if (!$model->save()) {
                        if (\common\helpers\App::isConsoleApplication()) {
                            echo "Error save OfferPrices. Errors: " . var_export($model->getErrors(), true) . PHP_EOL;
                        }

                        \Yii::error("Error while save mod prices recalculate. Err: " . var_export($model->getErrors(), true), 'common\helpers\Product::recalculateModification()');
                        throw new \yii\base\Exception("Error while save mod prices recalculate. Error: " . var_export($model->getErrors(), true));
                    } else {
                        if (\common\helpers\App::isConsoleApplication()) {
                            echo "Saved." . PHP_EOL;
                        }
                    }
                } else {
                    if (\common\helpers\App::isConsoleApplication()) {
                        echo "ProductPrices not changed. Skip save." . PHP_EOL;
                    }
                }

                //Оставим пуш даже если ничего не изменилось
                Yii::$app->queue->push(new UpdatePriceCardJob([
                    'id' => $model->parentContentElement->id,
                ]));

                return true;

            } else {
                //У товара нет типа цены значения которого нужны для обновления
                if (\common\helpers\App::isConsoleApplication()) {
                    echo "Error! Required element price type no found! [type='{$curPriceTypeId}']" . PHP_EOL;
                }

                \Yii::error("Error. Has no required price type [{$curPriceTypeId}] data for productID " . $model->id, 'common\helpers\Product::recalculateModification()');
//                throw new \yii\base\Exception("Error. Has no required price type data " . $model->id);
            }

        } else {
            //Отсутствие типа цены вполне рядовая ситуация, не будем забивать этим логи
//            \Yii::error("Error. Has no PRICE_ACTIVE property, id " . $model->id, 'common\helpers\Product::recalculateModification()');
//            throw new Exception("Error. Has no PRICE_ACTIVE property, id " . $model->id);

            if (\common\helpers\App::isConsoleApplication()) {
                echo "Error! No PRICE_ACTIVE prop!" . PHP_EOL;
            }
        }


        return false;
    }

    /**
     * Пересчет цен модификации
     * @param $model CmsContentElement
     * @param $lot CmsContentElement
     */
    //ПЕРЕСЧЕТ УЧИТЫВАЮЩИЙ УКАЗАНИЕ ТИПА ЦЕНЫ
    public static function recalculateModificationNewByType($model, $lot)
    {
        //return self::recalculateModificationOld($model, $lot);

        // todo If clear all shop_product_price and recreate give error duplicate Duplicate entry '1699997-6' for key 'unique_product_priceType'
        /** @var NewProduct $lot */
        if (!$model->isOffer()) {
            //\Yii::error("Error. Not Offer given!", 'common\helpers\Product::recalculateModification()');
            throw new Exception("Error. Not Offer given! // [{$model->id} | {$model->content_id}] {$model->name}");
        }

        //В консоле инфа по пересчету может быть полезной
        if (\common\helpers\App::isConsoleApplication()) {
            echo "Recalc price for offer [{$model->id}] {$model->name} // lotId = '{$lot->id}'" . PHP_EOL;
        }

        $cardsOnAir = SsMediaplanAirDayProductTime::getTodayAirProductsCardsIds(true);

        //Если цена базовая - пишем ее во все поля ss_shop_product_prices, цену ШШ пищшем равной базовой
        //Если цена не базовая - предварительно необходимо пересчитать цену ШШ (6 тип)

        //ss_shop_product_prices.ss_max_price - всегда цена ШШ
        //ss_shop_product_prices.price - берем из списка цен сущности соответствующую текущему типу цены

        // todo if not exist PRICE_ACTIVE, set from lot
//        $curPriceTypeId = $model->getRelatedPropertiesModel()->getAttribute('PRICE_ACTIVE');
        /** @var NewProduct $model */
//        $curPriceTypeId = $model->getPropertyActivePriceId();
        $curPriceTypeId = $model->new_price_active;
//        if (empty($curPriceTypeId)) {
//            $curPriceTypeId = $lot->getRelatedPropertiesModel()->getAttribute('PRICE_ACTIVE');
//        }
        if ($curPriceTypeId) {

            if (\common\helpers\App::isConsoleApplication()) {
                echo "PRICE_ACTIVE prop = {$curPriceTypeId}" . PHP_EOL;
            }

            //Выбираем методом что бы не взять цены из кеша
            $prices = $model->shopProductPrices;
            $prices = \common\helpers\ArrayHelper::index($prices, 'type_price_id');

            //Если в спиcке цен есть требуемого нам типа - пишем

            //* Страховка от случая когда текущая цена подменилась на ЦенуСайта1, а ее номинала нет *//
            if ($curPriceTypeId == self::BASE_PRICE_ID && empty($prices[self::BASE_PRICE_ID]) && !empty($prices[self::BASE_PRICE_ID_OLD])) {
                echo "New PriceBase Empty - Fill it as PriceBaseOld" . PHP_EOL;
                $prices[self::BASE_PRICE_ID] = $prices[self::BASE_PRICE_ID_OLD];
            }
            //* /Страховка от случая когда текущая цена подменилась на ЦенуСайта1, а ее номинала нет *//

            if (!empty($prices[$curPriceTypeId])) {
                $curPriceTypeModel = $prices[$curPriceTypeId];

                /** @var ShopProductPrice $priceBaseModel */

                //Для товаров в эфире в качестве базовой испольуем оригинальную (старую) базовую цену
//                $isProductOnAir = SsMediaplanAirDayProductTime::isProductOnAir($lot);
                $isProductOnAir = (bool)($cardsOnAir && isset($cardsOnAir[$model->parent_content_element_id]));
                if ($isProductOnAir) {
                    if (!empty($prices[self::BASE_PRICE_ID_OLD])) {
                        $priceBaseModel = $prices[self::BASE_PRICE_ID_OLD];
                    } else {
                        throw new \yii\base\Exception("ErrorEmptyBasePriceOld. Эфирный товар. Нет старой базовой цены!");
                    }
                } else {
                    if (!empty($prices[self::BASE_PRICE_ID])) {
                        $priceBaseModel = $prices[self::BASE_PRICE_ID];
                    } elseif (!empty($prices[self::BASE_PRICE_ID_OLD])) {
                        $priceBaseModel = $prices[self::BASE_PRICE_ID_OLD];
                    } else {
                        throw new \yii\base\Exception("ErrorEmptyBasePriceAll. Нет ни старой ни новой базовой цены!");
                    }
                }

                $model->new_price = $curPriceTypeModel->price;
                $model->new_price_old = $priceBaseModel->price;

                $model->new_discount_percent = 0;
                if ($model->new_price > 0 && $model->new_price_old > 0 && $model->new_price < $model->new_price_old) {
                    $model->new_discount_percent = max(0, round((($model->new_price_old - $model->new_price) / $model->new_price_old) * 100));
                }

                if ($model->isAttributeChanged('new_price') || $model->isAttributeChanged('new_price_old')) {
                    if (!$model->save()) {
                        if (\common\helpers\App::isConsoleApplication()) {
                            echo "Error save OfferPrices. Errors: " . var_export($model->getErrors(), true) . PHP_EOL;
                        }

                        \Yii::error("Error while save mod prices recalculate. Err: " . var_export($model->getErrors(), true), 'common\helpers\Product::recalculateModification()');
                        throw new \yii\base\Exception("Error while save mod prices recalculate. Error: " . var_export($model->getErrors(), true));
                    } else {
                        if (\common\helpers\App::isConsoleApplication()) {
                            echo "Saved." . PHP_EOL;
                        }
                    }
                } else {
                    if (\common\helpers\App::isConsoleApplication()) {
                        echo "ProductPrices not changed. Skip save." . PHP_EOL;
                    }
                }

                Yii::$app->queue->push(new UpdatePriceCardJob([
                    'id' => $model->parentContentElement->id,
                ]));

                return true;

            } else {
                //У товара нет типа цены значения которого нужны для обновления
                if (\common\helpers\App::isConsoleApplication()) {
                    echo "Error! Required element price type no found! [type='{$curPriceTypeId}']" . PHP_EOL;
                }

                \Yii::error("Error. Has no required price type [{$curPriceTypeId}] data for productID " . $model->id, 'common\helpers\Product::recalculateModification()');
//                throw new \yii\base\Exception("Error. Has no required price type data " . $model->id);
            }

        } else {
            //Отсутствие типа цены вполне рядовая ситуация, не будем забивать этим логи
//            \Yii::error("Error. Has no PRICE_ACTIVE property, id " . $model->id, 'common\helpers\Product::recalculateModification()');
//            throw new Exception("Error. Has no PRICE_ACTIVE property, id " . $model->id);

            if (\common\helpers\App::isConsoleApplication()) {
                echo "Error! No PRICE_ACTIVE prop!" . PHP_EOL;
            }
        }


        return false;
    }

    /**
     * @param $id
     * @throws Exception
     */
    public static function updatePriceCard($id)
    {
//        $model = NewProduct::findOne($id);
        $model = NewProduct::getDb()->cache(function ($db) use ($id) {
            return NewProduct::findOne($id);
        }, HOUR_1);
        if (self::recalculate($model)) {
            Yii::$app->queue->push(new UpdatePriceLotJob([
                'id' => $model->parentContentElement->id,
            ]));
        }

    }

    /**
     * @param $id
     * @throws Exception
     */
    public static function updatePriceLot($id)
    {
//        $model = NewProduct::findOne($id);
        $model = NewProduct::getDb()->cache(function ($db) use ($id) {
            return NewProduct::findOne($id);
        }, HOUR_1);
        self::recalculate($model);
    }

    public static function recalculateOld($model)
    {
        //Debug
        if ($model->id == 1691055 || $model->parent_content_element_id == 1691055) {
            \Yii::error("PriceRecalculate for productId={$model->id} [{$model->content_id} | parentId = {$model->parent_content_element_id}]", 'PriceRecalculate');
        }

//        $model = NewProduct::findOne($model->id);
        $price = null;
        $minPrice = null;
        $maxPrice = null;
        $discountPercent = null;
        /** @var NewProduct $model */
        $priceModel = $model->price;
        if (empty($priceModel)) {
            $priceModel = new SsShopProductPrice([
                'product_id' => $model->id,
                'type_price_id' => 0,
                'price' => 0,
                'min_price' => 0,
                'max_price' => 0,
            ]);

            //При сохранении модели в онце метода почему то ругается что в БД модель для этого продукта уже есть
            //Пробуем сохранять сразу после проверки
            if (!$priceModel->save()) {
                //\Yii::error('Error save price in lot or card id ' . $model->id . ', error ' . print_r($priceModel->errors, true), 'common\helpers\Product::recalculate()');
                echo('1 Error save price in lot or card id ' . $model->id . ' [priceModelIsNew=' . (int)($priceModel->isNewRecord) . '], error ' . print_r($priceModel->errors, true));
                throw new Exception('1 Error save price in lot or card id ' . $model->id . ' [priceModelIsNew=' . (int)($priceModel->isNewRecord) . '], error ' . print_r($priceModel->errors, true));
            }
        }

        $canSave = false;

        if (count($model->childrenContentElements) > 0) {
            foreach ($model->childrenContentElements as $children) {
                /** @var NewProduct $children */
                $id = $children->id;
                $childrenPrice = $children->price;
                if ($childrenPrice && $childrenPrice->price > 1) {
                    if ($children->isActiveForSale() || $model->new_quantity < 1) {
                        $canSave = true;
                        $priceModel->type_price_id = $childrenPrice->type_price_id;
                        $price = self::getMin($childrenPrice->price, $price);
                        $minPrice = self::getMin($childrenPrice->min_price, $minPrice);
                        // todo only card, change getMax when going tow product and remove card
                        $maxPrice = self::getMin($childrenPrice->max_price, $maxPrice);
                        //$discountPercent = self::getMax($childrenPrice->discount_percent, $discountPercent);
                    }
                }
            }


            $priceModel->price = $price;
            $priceModel->min_price = $minPrice;
            $priceModel->max_price = $maxPrice;
            //max_price может не быть если есть например только базовые цены на товары
            $priceModel->discount_percent = ($maxPrice && $price) ? max(0, round((($maxPrice - $price) / $maxPrice) * 100)) : 0;


            if ($canSave && ($priceModel->isAttributeChanged('price') || $priceModel->isAttributeChanged('max_price') || $priceModel->isAttributeChanged('type_price_id'))) {
                if (!$priceModel->save()) {
                    //\Yii::error('Error save price in lot or card id ' . $model->id . ', error ' . print_r($priceModel->errors, true), 'common\helpers\Product::recalculate()');
                    throw new Exception('Error save price in lot or card id ' . $model->id . ' [priceModelIsNew=' . (int)($priceModel->isNewRecord) . '], error ' . print_r($priceModel->errors, true));
                }

                //* cms_content_element.ATTRs save *//

                $model->new_price = $priceModel->price;
                $model->new_price_old = $priceModel->type_price_id == self::BASE_PRICE_ID ? $priceModel->price : $priceModel->max_price;
                $model->new_price_active = $priceModel->type_price_id;
                $model->new_discount_percent = $priceModel->discount_percent;

                if (!$model->save()) {
                    \Yii::error("Error while save lot or card id={$model->id} prices recalculate [cms_content_element]", __METHOD__);
                    throw new \yii\base\Exception("Error while save lot or card id={$model->id} prices recalculate [cms_content_element]");
                }

                //* /cms_content_element.ATTRs save *//
            }
            return true;
        }
        return false;

    }

    public static function recalculate($model)
    {
        //return self::recalculateOld($model);

        //Debug
        if ($model->id == 1691055 || $model->parent_content_element_id == 1691055) {
            \Yii::error("PriceRecalculate for productId={$model->id} [{$model->content_id} | parentId = {$model->parent_content_element_id}]", 'PriceRecalculate');
        }

//        $model = NewProduct::findOne($model->id);
        $price = null;
        $minPrice = null;
        $maxPrice = null;
        $discountPercent = null;
        /** @var NewProduct $model */

        $canSave = false;
        $typePriceId = false;

        $childs = \common\models\Product::find()->byParent($model->id);

        foreach ($childs->each() as $children) {
            /** @var NewProduct $children */
            $id = $children->id;
            if ($children->new_price > 1) {
                //return ($notPublic == Cms::BOOL_Y || $this->active == Cms::BOOL_N || $this->new_quantity < 1) ? false : true;
                //not_public только для лота, а он тут никогда не встречается
                //Проверить, возможно стоит учитывать флаг Не показывать из-за отсутствия фото
                $canSale = true;
                if ($children->active == \common\helpers\Common::BOOL_N || $children->new_quantity < 1) {
                    $canSale = false;
                }

//                if ($children->isActiveForSale() || $model->new_quantity < 1) {
                if ($canSale || $model->new_quantity < 1) {
                    $canSave = true;
                    $typePriceId = $children->new_price_active;
                    $price = self::getMin($children->new_price, $price);
//                    $minPrice = self::getMin($childrenPrice->min_price, $minPrice);
                    // todo only card, change getMax when going tow product and remove card
                    $maxPrice = self::getMin($children->new_price_old, $maxPrice);
                    //$discountPercent = self::getMax($childrenPrice->discount_percent, $discountPercent);
                }
            }
        }

        if ($canSave) {
            $discountPercent = ($maxPrice && $price) ? max(0, round((($maxPrice - $price) / $maxPrice) * 100)) : 0;

            $model->new_price = $price;
//            $model->new_price_old = $typePriceId == self::BASE_PRICE_ID ? $price : $maxPrice;
            $model->new_price_old = $maxPrice;
            $model->new_price_active = $typePriceId;
            $model->new_discount_percent = $discountPercent;

            if (($model->isAttributeChanged('new_price') || $model->isAttributeChanged('new_price_old') || $model->isAttributeChanged('new_price_active'))) {
                //* cms_content_element.ATTRs save *//
                if (!$model->save()) {
                    \Yii::error("Error while save lot or card id={$model->id} prices recalculate [cms_content_element]", __METHOD__);
                    throw new \yii\base\Exception("Error while save lot or card id={$model->id} prices recalculate [cms_content_element]");
                }

                //* /cms_content_element.ATTRs save *//
            }
            return true;
        }
        return false;

    }


    /**Пересчет цены ШШ для товара/карты/модификации
     *
     * @param $model CmsContentElement
     */
    public static function recalculatePriceSs($model)
    {
        if (\common\helpers\App::isConsoleApplication()) {
            echo "Recalc priceSS for element [{$model->id}] {$model->name}" . PHP_EOL;
        }

        $discountPriceSsRatio = 1.2;

        if ($prices = $model->shopProduct->shopProductPrices) {
            $prices = \common\helpers\ArrayHelper::index($prices, 'type_price_id');
            /** @var ShopProductPrice $priceBaseModel */
            $priceBaseModel = $prices[self::BASE_PRICE_ID] ?? null;

            //Если нет базовой цены - ошибка, это кретично
            if (!$priceBaseModel) {
                if (\common\helpers\App::isConsoleApplication()) {
                    echo "Error! Base price not found" . PHP_EOL;
                }

                \Yii::error("Base price not found", 'common\helpers\Product::recalculatePriceSs()');
                throw new \yii\base\Exception("Base price not found");
            }

            $priceSsModel = $prices[self::SS_PRICE_ID] ?? new ShopProductPrice([
                    'product_id' => $model->id,
                    'type_price_id' => self::SS_PRICE_ID,
                    'price' => $priceBaseModel->price
                ]);

            //Если базовая цена пустая - ошибка, это тоже кретично для перерасчета
            if ($priceBaseModel->price == 0) {
                if (\common\helpers\App::isConsoleApplication()) {
                    echo "Error! Base price = 0" . PHP_EOL;
                }
                \Yii::error("Error! Base price is 0, id " . $model->id, 'common\helpers\Product::recalculatePriceSs()');
//                throw new \yii\base\Exception("Error! Base price is 0, id ".$model->id);
            }

            //Для получения цены с *99 на конце
            $priceSs = round(round($priceBaseModel->price * $discountPriceSsRatio / 100) * 100 - 1);

            $priceSsModel->price = $priceSs;

            if ($priceSsModel->isAttributeChanged('price')) {
                if (!$priceSsModel->save()) {
                    if (\common\helpers\App::isConsoleApplication()) {
                        echo "Error! Can't save update SS price. Errors: " . var_export($priceSsModel->getErrors(), true) . PHP_EOL;
                    }

                    \Yii::error("Can't save update SS price", 'common\helpers\Product::recalculatePriceSs()');
                    throw new \yii\base\Exception("Can't save update SS price");
                } else {
                    if (\common\helpers\App::isConsoleApplication()) {
                        echo "Saved. PriceSS = '{$priceSsModel->price}'" . PHP_EOL;
                    }
                }

                return true;
            } else {
                if (\common\helpers\App::isConsoleApplication()) {
                    echo "PriceSS not changed [= '{$priceSsModel->price}' ]. Skip update." . PHP_EOL;
                }
            }

            //Пересчет цены
        } else {
            if (\common\helpers\App::isConsoleApplication()) {
                echo "Error! No prices found" . PHP_EOL;
            }

            \Yii::error("No prices found", 'common\helpers\Product::recalculatePriceSs()');
            throw new \yii\base\Exception("No prices found");
        }
        return false;
    }

    public static function getMax($one, $two)
    {
        if (is_null($one) && is_null($two)) {
            \Yii::warning('getMax price ont & price two is null', 'common\helpers\Product::getMax()');
//            throw new \yii\base\Exception('price ont & price two is null');
        }
        if (is_null($one)) {
            $one = $two;
        }
        if (is_null($two)) {
            $two = $one;
        }
        return $one > $two ? $one : $two;
    }

    public static function getMin($one, $two)
    {
        if (is_null($one) && is_null($two)) {
            \Yii::warning('getMin price one & price two is null', 'common\helpers\Product::getMin()');
//            throw new \yii\base\Exception('price ont & price two is null');
        }
        if (is_null($one) || $one < 1) {
            $one = $two;
        }
        if (is_null($two) || $two < 1) {
            $two = $one;
        }
        return $one < $two ? $one : $two;
    }

    public static function getLotName($product)
    {
        if (is_numeric($product)) {
            $product = ProductModel::getFromCache($product);
        }

        if ($product instanceof ProductModel) {
            $lot = $product->isLot() ? $product : self::getLot($product->id);
            return $lot ? $lot->name : '';
        }

        return '';
    }

    /** Получить номер лота из любой сущности связанной с товаром
     *
     * Номер лота точно есть в самом лоте, берем оттуда. Возможно стоит перенести и во все дочерние сущности.
     *
     * @param $product
     * @return string
     */
    public static function getLotNum($product)
    {
        if (is_numeric($product)) {
            $product = ProductModel::getFromCache($product);
        }

        if ($product instanceof ProductModel) {
            $lot = $product->isLot() ? $product : self::getLot($product->id);
            return $lot ? $lot->new_lot_num : '';
        }

        return '';
    }

    public static function getLotNumByElement(int $id)
    {
        $lotNumId = self::LOT_NUM_ID;

        $sql = "SELECT `value` FROM `cms_content_element_property` WHERE `element_id` = :id
        AND property_id = :lotNumId";

        $row = \Yii::$app->db->createCommand($sql, [
            ':id' => $id,
            ':lotNumId' => $lotNumId,
        ])->queryOne();

        if (isset($row['value']) && $row['value']) {
            return $row['value'];
        } else {
            return null;
        }
    }


    /**
     * Ищет id товара по номеру лота
     * @param string lotNum
     * @return string|null
     */
    public static function getElementByLotNum(string $lotNum)
    {
        if (strlen($lotNum) < Strings::NUM_DIGITS_LOT)
            return null;

        $lotNumId = self::LOT_NUM_ID;

        $sql = "select element_id from cms_content_element_property ccep
                left join cms_content_property ccp on ccep.property_id = ccp.id
                left join shop_product sp on sp.id = ccep.element_id
                where ccp.id = :lotNumId AND ccep.value = :lotNum
                order by sp.quantity desc";

        $row = \Yii::$app->db->createCommand($sql, [
            ':lotNumId' => $lotNumId,
            ':lotNum' => $lotNum,
        ])->queryOne();


        if (isset($row['element_id']) && $row['element_id']) {
            return $row['element_id'];
        } else {
            return null;
        }
    }


    /**
     * Обновляем связанные свойства из content_element_property в колонки таблицы cms_content_element
     *
     * @param $propColumn
     * @param $propId
     * @param array $contentId
     * @return int
     */
    public static function updatePropFromContentProperty($propColumn, $propId, $contentId = [PRODUCT_CONTENT_ID, CARD_CONTENT_ID, OFFERS_CONTENT_ID])
    {

        $productContentId = implode(',', (array)$contentId);

        $affected = \Yii::$app->db->createCommand("
UPDATE cms_content_element AS product
  LEFT JOIN cms_content_element_property AS prop_data ON prop_data.property_id = :propId AND prop_data.element_id=product.id
SET product.{$propColumn}=prop_data.value
WHERE
  product.content_id IN ({$productContentId})
  AND (
        product.{$propColumn} IS NULL
        OR product.{$propColumn}=''
      )",
            [
                ':propId' => $propId,
            ])->execute();

        return $affected;
    }

    public static function updatePropFromNonContentProperty($propCode, $overwrite = false)
    {

        switch ($propCode) {
            case 'new_guid':

                $overwriteCond = $overwrite ? "" : "WHERE " . $propCode . " IS NULL";

                $affected = \Yii::$app->db->createCommand("
UPDATE cms_content_element as df
  LEFT JOIN ss_guids ON guid_id = ss_guids.id
SET new_guid= ss_guids.guid
{$overwriteCond}")
                    ->execute();

                break;
            case 'new_quantity':

                $overwriteCond = $overwrite ? '' : "WHERE product." . $propCode . " IS NULL OR product.new_quantity=''";

                $affected = \Yii::$app->db->createCommand("
UPDATE cms_content_element AS product
  LEFT JOIN shop_product ON shop_product.id=product.id
SET product.new_quantity=shop_product.quantity
{$overwriteCond}
")
                    ->execute();

                break;
            case 'new_price':
            case 'new_price_old':

                $overwriteCond = $overwrite ? '' : "WHERE product." . $propCode . " IS NULL OR product." . $propCode . "=''";

                $affected = \Yii::$app->db->createCommand("
UPDATE cms_content_element AS product
  LEFT JOIN ss_shop_product_prices AS prices ON prices.product_id=product.id
SET
  product.new_price_active=prices.type_price_id,
  product.new_price=prices.price,
  product.new_price_old=prices.max_price,
  product.new_discount_percent=prices.discount_percent
{$overwriteCond}
")
                    ->execute();

                break;
            default:
                $affected = 0;
                break;
        }

        return $affected;
    }

    /** Метод получения лота. (Без использования Скекс цмс)
     *
     * @param $id
     * @return ProductModel | bool
     */
    public static function getLot($id, $defaultValue = false)
    {
        //TODO ДОБАВИТЬ КЕШ!
        $model = ProductModel::getFromCache($id);
        if (!$model) {
            return null;
        }

        if (!$model->isLot() && $model->parent_content_element_id) {
            $model = ProductModel::getFromCache($model->parent_content_element_id);
            if ($model && !$model->isLot() && $model->parent_content_element_id) {
                $model = ProductModel::getFromCache($model->parent_content_element_id);
            }
        }

        return $model && $model->isLot() ? $model : $defaultValue;
    }

    public static function updateQuantity($id)
    {
        return self::recalculateQuantity(ProductModel::getFromCache($id));
    }

    /** Пересчет всех сущностей по товару (по факту все карточки и выше)
     *
     * @param $id - лот/карточка/модификация
     * @return bool
     */
    public static function updateQuantityAll($id)
    {
        $lot = self::getLot($id);

        if ($lot) {

            if (\common\helpers\App::isConsoleApplication()) {
                echo "UpdateQuantityAll for lot [{$lot->id}] {$lot->name}" . PHP_EOL;
            }

            $cards = $lot->getChildrenContentElements()->all();

            if ($cards) {
                foreach ($cards as $card) {
                    self::updateQuantity($card->id);
                }
            } else {
                if (\common\helpers\App::isConsoleApplication()) {
                    echo "UpdateQuantityAll. Can't find cards for lot [{$lot->id}] {$lot->name}" . PHP_EOL;
                }
            }
        } else {
            if (\common\helpers\App::isConsoleApplication()) {
                echo "UpdateQuantityAll. Can't find lot via searched id={$id}" . PHP_EOL;
            }
        }

        return true;
    }


    public static function recalculateQuantity($model)
    {
        /** @var ProductModel $model */

        if (!$model) {
            \Yii::error("Quantity recalc - Empty model!", __METHOD__);
            return false;
        }

        //В модификации пересчитывать нечего, будем переходить сразу к родителю
        if ($model->isLot() || $model->isCard()) {

            //Суть проста - записать сумму остатков из дочерних элементов в текущий
            //Есть исключение! Для карточки, если есть Активные НЕ базовые модификации (модификаций больше одной уже об этом свидетельствует),
            //то учитываем только остатки в них, возможные остатки в базовой модификации игнорируем!
            //Если активна одна моификация (базовая) - то берем остатки только из нее,

            //Все активные дети, только из таких можем брать остатки
            $childs = ProductModel::find()->byParent($model->id)->onlyActive()->all();


            if ($childs) {
                $model->new_quantity = \common\helpers\ArrayHelper::arraySumColumn($childs, 'new_quantity');

                //Если у нас карточка товара, то если есть не базовые модификации (то есть их больше 1 ибо базовая всегда активна) то остатки из базовой надо вычесть из общего кол-ва
                if ($model->isCard() && count($childs) > 1) {
                    //Ищем базовый элемент
                    $baseQuantity = 0;
                    foreach ($childs as $child) {
                        if ($child->is_base == 'Y') {
                            $baseQuantity += $child->new_quantity;
                        }
                    }

                    $model->new_quantity = $model->new_quantity - $baseQuantity;
                }
            }

            $model->updateAttributes(['new_quantity']);
        }

        if ($model->parent_content_element_id) {
            if (\common\helpers\App::isConsoleApplication()) {
                Yii::$app->queueProduct->push(new UpdateQuantityJob([
                    'id' => $model->parent_content_element_id,
                ]));
            } else {
                // why not queue?
                // Для оперативного пересчета. Используется в админке.
                self::updateQuantity($model->parent_content_element_id);
            }
        }

        return true;
    }

    public static function updateSeoLotWithChilds($id)
    {
        $product = ProductModel::findOne($id);

        if ($product && $product->isLot()) {
            //Добавляем сам лот на обновление
            self::updateSeo($product->id);

            $cards = $product->getChildrenContentElements()->all();

            //Добавляем карты на обновление
            if ($cards) {
                foreach ($cards as $card) {
                    var_dump("updateSeoCard = {$card->id} | {$card->name}");
                    self::updateSeo($card->id);
                }
            }

            //СЕО модификаций пока не используем так что пересчитывать пока не требуется
        }

        return;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public static function updateSeo($id)
    {
        $model = \common\models\Product::findOne($id);
        if ($model) {
            $model->forceUpdateSeoFields = true;
            $model->trigger(ActiveRecord::EVENT_AFTER_INSERT);
        }
        return true;
    }

    //Синхронизирует not_public -> new_not_public
    public static function syncNewNotPublic()
    {
        if (App::isConsoleApplication()) {
            echo "Синхранизация not_public -> new_not_public" . PHP_EOL;
        }

        $productsNotPublicQuery = CmsContentElementProperty::find()
            ->select(['element_id'])
            ->where([
                'property_id' => 83,
                'value' => 'Y',
            ])
            ->asArray();

        $productsNotPublic = $productsNotPublicQuery->all();

        if ($productsNotPublic) {
            $productsNotPublicIds = ArrayHelper::getColumn($productsNotPublic, 'element_id');

            if (App::isConsoleApplication()) {
                echo "Товаров с not_public=Y - " . count($productsNotPublicIds) . PHP_EOL;
            }

            $affected = ProductModel::updateAll(['new_not_public' => 1], ['id' => $productsNotPublicIds]);

            if (App::isConsoleApplication()) {
                echo "Обновлено товаров - {$affected}" . PHP_EOL;
            }
        } else {
            if (App::isConsoleApplication()) {
                echo "Обновлять нечего" . PHP_EOL;
            }
        }

        return $affected ?? 0;
    }

    //Обновление плашек товаров относительно медиаплана
    public static function updateBadge1()
    {
        if (App::isConsoleApplication()) {
            echo "Обновление плашек №1" . PHP_EOL;
        }

        //1) Определяем ЦТС
        //2) Выбираем товары Сегодня в эфире
        //3) Выбираем товары за прошедшую неделю

        //Сбрасываем все
        $affected = ProductModel::updateAll(['badge_1' => 0], ['!=', 'badge_1', 0]);

        if (App::isConsoleApplication()) {
            echo "Сбрасываю флаги плашек. Затронуто элементов - {$affected}" . PHP_EOL;
        }

        //* CTS *//
        if (true) {
            /** @var CmsContentElementQuery $productsCtsQuery */
            $productsCtsQuery = \common\helpers\Product::getCtsProductsQuery();

            if ($productsCtsIds = $productsCtsQuery->column()) {
                $affectedLots = ProductModel::updateAll(['badge_1' => ProductModel::BADGE1_CTS], ['id' => $productsCtsIds]);
                $affectedCards = ProductModel::updateAll(['badge_1' => ProductModel::BADGE1_CTS], ['parent_content_element_id' => $productsCtsIds]);

                if (App::isConsoleApplication()) {
                    echo "Устанавливаю флаги плашек ЦТС. Затронуто лотов/карточек - {$affectedLots} / {$affectedCards}" . PHP_EOL;
                }
            } else {
                if (App::isConsoleApplication()) {
                    echo "Товаров ЦТС не найдено" . PHP_EOL;
                }
            }
        }


        //* /CTS *//

        //* FLASH_PRICE *//

        if (true) {
//            $flashPriceCardsIds = BUFECommFlashPrice::getCardsIds();
            $flashPriceLotsIds = BUFECommFlashPrice::getLotIds();

            if (App::isConsoleApplication()) {
                echo "Устанавливаю флаги плашек Выгода на час. Лотов из аналитики пришло - " . count($flashPriceLotsIds) . PHP_EOL;
            }

            if ($flashPriceLotsIds) {
                $affectedLots = ProductModel::updateAll(['badge_1' => ProductModel::BADGE1_FLASH_PRICE], ['badge_1' => 0, 'id' => $flashPriceLotsIds]);
                $affectedCards = ProductModel::updateAll(['badge_1' => ProductModel::BADGE1_FLASH_PRICE], ['badge_1' => 0, 'parent_content_element_id' => $flashPriceLotsIds]);

                if (App::isConsoleApplication()) {
                    echo "Устанавливаю флаги плашек Выгода на час. Затронуто лотов/карточек - {$affectedLots} / {$affectedCards}" . PHP_EOL;
                }
            }
        }

        //* /FLASH_PRICE *//

        //* PRIME *//

        //Сначала устанавливаем Выгоду на час, так как с прайм это один список товаров и надо что бы выгода заняла часть, иначе потом не будет "свободных" этих товаров

        if (Setting::getUsePricePrime()){
            $cardsPrimeQuery = ProductModel::getCardsWithPrimePriceQuery();
            $lotsPrimeIds = $cardsPrimeQuery->select('parent_content_element_id')->groupBy(['parent_content_element_id'])->column();

            if ($lotsPrimeIds){
                $affectedLots = ProductModel::updateAll(['badge_1' => ProductModel::BADGE1_PRIME], ['badge_1' => 0, 'id' => $lotsPrimeIds]);
                $affectedCards = ProductModel::updateAll(['badge_1' => ProductModel::BADGE1_PRIME], ['badge_1' => 0, 'parent_content_element_id' => $lotsPrimeIds]);

                if (App::isConsoleApplication()) {
                    echo "Устанавливаю флаги плашек PRIME. Затронуто лотов/карточек - {$affectedLots} / {$affectedCards}" . PHP_EOL;
                }
            }

        }

        //* /PRIME *//

        //* ON AIR THIS DAY *//

        /** @var SsMediaplanAirDayProductTimeQuery $airProductsThisDayQuery */
        $airProductsThisDayQuery = (new SsMediaplanAirDayProductTime())->getAirProductsQuery();
        $airProductsThisDayQuery->select('lot_id')->groupBy(['lot_id']);

        if ($productsThisDayIds = $airProductsThisDayQuery->column()) {
            $affectedLots = ProductModel::updateAll(['badge_1' => ProductModel::BADGE1_ON_AIR_DAY], ['badge_1' => 0, 'id' => $productsThisDayIds]);
            $affectedCards = ProductModel::updateAll(['badge_1' => ProductModel::BADGE1_ON_AIR_DAY], ['badge_1' => 0, 'parent_content_element_id' => $productsThisDayIds]);

            if (App::isConsoleApplication()) {
                echo "Устанавливаю флаги плашек Сегодня в эфире. Затронуто лотов/карточек - {$affectedLots} / {$affectedCards}" . PHP_EOL;
            }
        }

        //* /ON AIR THIS DAY *//

        //* ON AIR THIS WEEK *//

        /** @var SsMediaplanAirDayProductTimeQuery $airProductsThisWeekQuery */
        $airProductsThisWeekQuery = (new SsMediaplanAirDayProductTime())->getAirProductsLastWeekQuery();
        $airProductsThisWeekQuery = $airProductsThisWeekQuery->select('lot_id')->groupBy(['lot_id']);

        if ($productsThisWeekIds = $airProductsThisWeekQuery->column()) {
            $affectedLots = ProductModel::updateAll(['badge_1' => ProductModel::BADGE1_ON_AIR_WEEK], ['badge_1' => 0, 'id' => $productsThisWeekIds]);
            $affectedCards = ProductModel::updateAll(['badge_1' => ProductModel::BADGE1_ON_AIR_WEEK], ['badge_1' => 0, 'parent_content_element_id' => $productsThisWeekIds]);

            if (App::isConsoleApplication()) {
                echo "Устанавливаю флаги плашек В эфире на этой неделе. Затронуто лотов/карточек - {$affectedLots} / {$affectedCards}" . PHP_EOL;
            }
        }

        //* /ON AIR THIS WEEK *//

        return true;
    }

    public static function updateBadge2()
    {
        if (App::isConsoleApplication()) {
            echo "Обновление плашек №2" . PHP_EOL;
        }

        //Сбрасываем все
        $affected = ProductModel::updateAll(['badge_2' => 0], ['!=', 'badge_2', 0]);

        if (App::isConsoleApplication()) {
            echo "Сбрасываю флаги плашек. Затронуто элементов - {$affected}" . PHP_EOL;
        }

        //* Bestseller *//

        $productsBestsellers = ProductAbc::getBestseller();
        if ($productsBestsellers) {
            $productsBestsellersIds = ArrayHelper::getColumn($productsBestsellers, 'id');

            $affectedLots = ProductModel::updateAll(['badge_2' => ProductModel::BADGE2_BESTSELLER], ['badge_2' => 0, 'id' => $productsBestsellersIds]);
            $affectedCards = ProductModel::updateAll(['badge_2' => ProductModel::BADGE2_BESTSELLER], ['badge_2' => 0, 'parent_content_element_id' => $productsBestsellersIds]);

            if (App::isConsoleApplication()) {
                echo "Устанавливаю флаги плашек Бестселлер. Затронуто лотов/карточек - {$affectedLots} / {$affectedCards}" . PHP_EOL;
            }
        }

        //* /Bestseller *//

        //* Hit *//

        $productsHit = ProductAbc::getHit();
        if ($productsHit) {
            $productsHitIds = ArrayHelper::getColumn($productsHit, 'id');

            $affectedLots = ProductModel::updateAll(['badge_2' => ProductModel::BADGE2_HIT], ['badge_2' => 0, 'id' => $productsHitIds]);
            $affectedCards = ProductModel::updateAll(['badge_2' => ProductModel::BADGE2_HIT], ['badge_2' => 0, 'parent_content_element_id' => $productsHitIds]);

            if (App::isConsoleApplication()) {
                echo "Устанавливаю флаги плашек Хит. Затронуто лотов/карточек - {$affectedLots} / {$affectedCards}" . PHP_EOL;
            }
        }

        //* /Hit *//

        //* Favorite *//

        $productsFavorite = ProductAbc::getFavorite();
        if ($productsFavorite) {
            $productsFavoriteIds = ArrayHelper::getColumn($productsFavorite, 'id');

            $affectedLots = ProductModel::updateAll(['badge_2' => ProductModel::BADGE2_FAVORITE_PRODUCT], ['badge_2' => 0, 'id' => $productsFavoriteIds]);
            $affectedCards = ProductModel::updateAll(['badge_2' => ProductModel::BADGE2_FAVORITE_PRODUCT], ['badge_2' => 0, 'parent_content_element_id' => $productsFavoriteIds]);

            if (App::isConsoleApplication()) {
                echo "Устанавливаю флаги плашек Любимый товар. Затронуто лотов/карточек - {$affectedLots} / {$affectedCards}" . PHP_EOL;
            }
        }

        //* /Favorite *//

        //* Super discount *//
        //Плашка Суперскидка ставим на товары с пустой второй плашкой и скидкой более N (Product::BADGE2_SUPER_DISCOUNT_LIMIT)
        //В качестве доп проверки проверяем еще и соотношение цен текущей и зачеркнутой
        $productsSuperDiscountQuery = ProductModel::find()
            ->select('id')
            ->where(['badge_2' => 0])
            ->andWhere(['>', 'new_discount_percent', ProductModel::BADGE2_SUPER_DISCOUNT_LIMIT])
            ->andWhere('new_price < new_price_old');

        $affected = ProductModel::updateAll(['badge_2' => ProductModel::BADGE2_SUPER_DISCOUNT], ['badge_2' => 0, 'id' => $productsSuperDiscountQuery->column()]);

        if (App::isConsoleApplication()) {
            echo "Устанавливаю флаги плашек Суперскидка. Затронуто лотов/карточек - {$affected}" . PHP_EOL;
        }

        //* /Super discount *//

        return true;
    }

    public static function updateSortWeight()
    {
        if (App::isConsoleApplication()) {
            echo "Обновление сортировочного веса" . PHP_EOL;
        }

        //Сбрасываем все
        $affected = ProductModel::updateAll(['sort_weight' => 0], ['!=', 'sort_weight', 0]);

        if (App::isConsoleApplication()) {
            echo "Сбрасываю значение. Затронуто элементов - {$affected}" . PHP_EOL;
        }

        $sortUpdateData = [];

        //Все товары у которых есть плашки
        $productsQuery = ProductModel::find()
            ->where(['>', 'badge_1', 0])
            ->orWhere(['>', 'badge_2', 0]);

        /** @var ProductModel $product */
        //Собираем данные для обновления
        foreach ($productsQuery->each() AS $index => $product) {
            $sortWeight = $product->badge_1 > $product->badge_2 ? $product->badge_1 : $product->badge_2;

            $sortUpdateData[$sortWeight][] = $product->id;
        }

        if ($sortUpdateData) {
            ksort($sortUpdateData);
            foreach ($sortUpdateData as $sortWeight => $productsIds) {
                $affected = ProductModel::updateAll(['sort_weight' => $sortWeight], ['id' => $productsIds]);

                if (App::isConsoleApplication()) {
                    echo "Сортировочный вес '{$sortWeight}' выставлен для товаров: {$affected}" . PHP_EOL;
                }
            }
        }

        if (App::isConsoleApplication()) {
            echo "Done" . PHP_EOL;
        }

        return true;
    }

    public static function getCtsProductsQuery($time = false)
    {
        $ctsShares = SsShare::find()
            ->select('bitrix_product_id')
            ->andWhere(['banner_type' => SsShare::BANNER_TYPE_CTS])
//            ->andWhere(['not', ['image_id' => null]])
            ->andWhere(['not', ['active' => \common\helpers\Common::BOOL_N]])
            ->andWhere('begin_datetime <= :time AND end_datetime >= :time', [
                ':time' => $time ?: time(),
            ]);

        return ProductModel::find()
            ->andWhere(['bitrix_id' => $ctsShares]);
    }

    public static function updatePriceAll($productId)
    {
        if (\common\helpers\App::isConsoleApplication()) {
            echo("Обновляю цены связанные с товаром ID={$productId}" . PHP_EOL);
        }
        $offers = \common\models\Product::getProductOffers($productId);

        if ($offers) {

            if (\common\helpers\App::isConsoleApplication()) {
                echo("Модификаций для пересчета: " . count($offers) . PHP_EOL);
            }

            $productsQuery = \common\models\Product::find()
                ->onlyModification()
                ->andWhere(['id' => ArrayHelper::getColumn($offers, 'id')]);

            $i = 0;
            foreach ($productsQuery->each() as $offer) {
                $i++;
                try {
                    Yii::$app->queue->push(new UpdatePriceJob([
                        'id' => $offer->id,
                    ]));

                    if (\common\helpers\App::isConsoleApplication()) {
                        echo("{$i}) Добавлено задание на пересчет для модификации ID={$offer->id}" . PHP_EOL);
                    }

                } catch (\Exception $e) {
                    echo('Error ' . $e->getMessage() . PHP_EOL);
                }
            }
        } else {

            if (\common\helpers\App::isConsoleApplication()) {
                echo("Не нахожу модификации связанные с товаром ID={$productId}" . PHP_EOL);
            }

            return false;
        }

        if (\common\helpers\App::isConsoleApplication()) {
            echo('Done' . PHP_EOL);
        }

        return true;
    }

    //После смены логики базовой цены, необходимо обновить товары не затронутые переоценкой что бы выставить Цену сайта 1 там, где сейчас цена Базовая
    public static function updateBasePrice($limit = 100, $id = false)
    {
        $offersQuery = ProductModel::find()
            ->alias('offers')
            ->select('offers.id')
            ->innerJoin(ProductModel::tableName() . ' AS cards', "cards.id=offers.parent_content_element_id")
            ->innerJoin(ProductModel::tableName() . ' AS products', "products.id=cards.parent_content_element_id")
            ->innerJoin(CmsContentElementProperty::tableName() . ' AS price_active', "offers.id = price_active.element_id")
            ->andWhere([
                'offers.content_id' => ProductModel::MOD,
                'offers.new_price_active' => Product::BASE_PRICE_ID_OLD,
                'offers.active' => \common\helpers\Common::BOOL_Y,
                'cards.active' => \common\helpers\Common::BOOL_Y,
                'products.active' => \common\helpers\Common::BOOL_Y,
                'price_active.property_id' => 174, //PRICE_ACTIVE свойство у модификации
                'price_active.value' => Product::BASE_PRICE_ID_OLD
            ])
            ->andWhere(['>', 'products.new_quantity', 0])
            ->andWhere(['>', 'products.new_price', 1]);

        if ($id) {
            if ($lot = self::getLot($id)) {
                $offersQuery->andWhere(['products.id' => $lot->id]);
            }
        }

        if ($limit && is_numeric($limit)) {
            $offersQuery->limit($limit);
        }

        $i = 0;
        foreach ($offersQuery->each() as $offer) {
            $i++;

            $elementPropertyPriceActive = CmsContentElementProperty::findOne([
                'property_id' => 174,
                'element_id' => $offer->id,
                'value' => Product::BASE_PRICE_ID_OLD
            ]);

            if (true || $id) {
                echo "{$i}) Обновляю модификацию '{$offer->id}', свойство №" . ($elementPropertyPriceActive ? $elementPropertyPriceActive->id : '---') . PHP_EOL;
            }

            if ($elementPropertyPriceActive) {
                $elementPropertyPriceActive->value = Product::BASE_PRICE_ID;

                if ($elementPropertyPriceActive->save(false)) {
                    //Пересчет цен
                    Yii::$app->queue->push(new UpdatePriceJob([
                        'id' => $offer->id,
                    ]));
                } else {
                    echo "Error save offer price prop. ElementId={$offer->id}, errors: " . var_export($elementPropertyPriceActive->getErrors(), true) . PHP_EOL;
                }
            }
        }

        return true;
    }

    public static function hideNoImageCards()
    {
        $unhideAffectedImage = \common\models\Product::updateAll(['hide_from_catalog_image' => 0], ['hide_from_catalog_image' => 1]);

        if (App::isConsoleApplication()) {
            echo "Отменено скрытие из каталога (лоты + карточки) : " . $unhideAffectedImage . PHP_EOL;
        }
        //получить все карточки без картинок
        $cards = \common\models\Product::find()
            ->select([
                'id',
            ])
            ->onlyCard()
            ->onlyActive()
            ->imageIdIsNull();

        //todo данная проверка пока не нужна
//        foreach ($cards->each() as $card) {
//
//
//            //проверяем количество карточек у лота, если там только одна карточка - не скрываем иначе скрываем
//            $count = \common\models\Product::find()
//                ->onlyCard()
//                ->onlyActive()
//                ->andWhere(['parent_content_element_id' => $card->parent_content_element_id])
//                ->count();
//            if($count > 1){
//
//            if (App::isConsoleApplication()) {
//                echo "Скрываем карточку : " . $card->id . PHP_EOL;
//            }
//            $hidecards[] = $card->id;
//                \common\models\Product::updateAll(['hide_from_catalog_image' => 1], ['id' => $card->id]);
//            }
//        }

        $affectedRows = \common\models\Product::updateAll(['hide_from_catalog_image' => 1], ['id' => $cards->column()]);
        if (App::isConsoleApplication()) {
            echo "Скрыли {$affectedRows} без картинок" . PHP_EOL;
        }
    }
}
