<?php

namespace console\controllers\queues\jobs\products;

use common\helpers\ArrayHelper;
use common\helpers\Common as CommonHelper;
use common\helpers\Product as ProductHelper;
use common\models\cmsContent\CmsContentElement;
use common\models\cmsContent\CmsContentProperty;
use common\models\ShopTypePrice as ShopTypePriceModel;
use common\models\SsMediaplanAirDayProductTime;
use console\controllers\queues\jobs\Job;
use console\jobs\UpdateNewFieldsJob;
use console\jobs\UpdatePriceCardJob;
use console\jobs\UpdatePriceJob;
use modules\shopandshow\models\newEntities\products\PricesList;
use modules\shopandshow\models\shop\ShopTypePrice;
use skeeks\cms\models\CmsContentElementProperty;
use skeeks\cms\shop\models\ShopProductPrice;
use Yii;

class Price extends Job
{
    private $runtimeLimit = 5;

    /**
     *
     * @param \yii\queue\Queue $queue
     * @param string $guid
     *
     * @return bool
     * @throws \Exception
     */
    public function execute($queue, &$guid)
    {
        Job::dump("Start PriceJob ");

        $startTime = microtime(true);
        if ($this->prepareData($queue)) {
            $guid = $this->data['Data']['OffcntGuid'];

            //$result = $this->addPrice();
//            $result = $this->addPriceNew();
            $result = $this->addPriceNewV2();

            Yii::$app->queueProduct->push(new UpdateNewFieldsJob([
                'data' => $queue,
            ]));

            $totalTime = round((microtime(true) - $startTime), 5);

            Job::dump(">>> RuntimeFull: {$totalTime} sec");

            if ($totalTime > $this->runtimeLimit) {
                \Yii::error("Queue price log runtime! Data: " . var_export($this->data, true), "console\controllers\queues\jobs\products");
            }

            return $result;
        }

        return false;
    }

    //Вариант не учитывающий указания по типу цены
    //Учитываются только цены типа Цена сайта 1 и 2
    protected function addPriceNewV2()
    {
        $info = $this->data['Info'];
        $data = $this->data['Data'];

        CommonHelper::startTimer('GLOBAL');

        $guid = trim($data['OffcntGuid']);

        Job::dump('---ProductPrice----');
        Job::dump('Guid: ' . $guid);
        Job::dump('Price count: ' . sizeof($data['Price']));
        Job::dump('PricesVary: ' . ($data['PricesVary'] ? 'Y' : 'N'));
        Job::dump('PriceMainGuid: ' . ($data['PriceMainGuid'] ?: '<<EMPTY>>'));

        $startTimePriceList = microtime(true);

        $productPrices = new PricesList();

        $product = $productPrices->getOrCreateElement($guid);
        if ($product == false) {
            Job::dump(' failed to get product');
            return false;
        }


        $productPrices->setCmsContentElement($product);
        $productPrices->setPricesList((array)$data['Price']);

        $productPrices->priceMainGuid = $data['PriceMainGuid'];
        $productPrices->pricesVary = $data['PricesVary'];

        $pricesByGuid = $productPrices->prices ? ArrayHelper::index($productPrices->prices, 'TypeGuid') : [];
        $priceTypeSite1 = ShopTypePriceModel::getPriceTypeMainDataById(ShopTypePriceModel::PRICE_TYPE_SITE1_ID);
        $priceTypeSite2 = ShopTypePriceModel::getPriceTypeMainDataById(ShopTypePriceModel::PRICE_TYPE_SITE2_ID);
        $priceTypeBaseOld = ShopTypePriceModel::getPriceTypeMainDataById(ShopTypePriceModel::PRICE_TYPE_BASE_ID_OLD);

        //Нам в любом случае пригодится текущий тип цены

        $startTimePriceMainGet = microtime(true);

        //ТИПА ЦЕНЫ МОЖЕТ И НЕ БЫТЬ!

        //* ТИП ЦЕНЫ *//

        $priceTypeMain = false;
        if (!empty($productPrices->priceMainGuid)) {
            //$shopTypePrice = $productPrices->ensureShopTypePrice($productPrices->priceMainGuid);
            //$shopTypePriceMain = ShopTypePrice::getShopTypePriceByGuid($productPrices->priceMainGuid, true);
            $priceTypeMain = ShopTypePriceModel::getPriceTypeMainDataByGuid($productPrices->priceMainGuid, true);
            Job::dump("PriceMainIs: [{$priceTypeMain['id']}] {$priceTypeMain['name']}");

            if ($priceTypeMain) {

                if ($priceTypeMain['id'] == ProductHelper::BASE_PRICE_ID_OLD) {
                    Job::dump("PriceMain is OLD! Correcting...");//$shopTypePriceMainProper = ShopTypePrice::findOne(\common\helpers\Product::BASE_PRICE_ID);
                    if ($priceTypeSite1) {
                        $priceTypeMain = $priceTypeSite1;
                        Job::dump("PriceMainPROPER Is: [{$priceTypeMain['id']}] {$priceTypeMain['name']}");
                    } else {
                        Job::dump("Не могу найти новый базовый тип цены! Использую старый.");
                    }
                }

            } else {
                Job::dump("Не могу найти базовый тип цены! GUID: '{$productPrices->priceMainGuid}'");
            }

            $totalTimePriceMainGet = round((microtime(true) - $startTimePriceMainGet), 5);
            Job::dump(" :time[PriceMainGet] {$totalTimePriceMainGet} sec");
        } else {
            Job::dump("PriceMainIs: EMPTY!");
            //Отсутствие типа цены не такая уж рекость, так что это нюанс, а не ошибка
            //\Yii::error("PriceMainIs EMPTY! Product GUID='{$guid}'", "");
        }

        //* /ТИП ЦЕНЫ *//


        $totalTimePriceList = round((microtime(true) - $startTimePriceList), 5);

        Job::dump(" :time[Prepare] = {$totalTimePriceList} sec");

        //****** ОБНОВЛЕНИЕ ТЕКУЩЕГО ЭЛЕМЕНТА (тип цены и номиналы) ******//

        //1С пропускаем теперь всегда, всем заведует КФСС
        if ($info['Source'] == '1C') {
            Job::dump("1C source. Skip processing.");
        }

        if ($info['Source'] == 'KFSS') { //Если что то не так - вернуть == '1C'
            if ($product->isOffer()) {

                if (!empty($priceTypeMain)) {
                    $startTimePriceMainUpdate = microtime(true);
                    //$this->updatePriceActive($product, $shopTypePriceMain);
                    //Тип цены изменился

                    //НЕ СОХРАНЯЕМ ИЗМЕНЕНИЕ ТАК КАК ТИП ЦЕНЫ ПЕРЕДЕЛАН НА РАСЧЕТНО-АНАЛИТИЧЕСКИЙ!
                    if (false) {
                        if ($priceTypeMain && $product->new_price_active != $priceTypeMain['id']) {
                            $product->new_price_active = $priceTypeMain['id'];
                        }
                    }


                    //Сохранение в этом месте преждевременно, так что таймер не особо актуален, но пока оставим
                    $totalTimePriceMainUpdate = round((microtime(true) - $startTimePriceMainUpdate), 5);
                    Job::dump(" :time[PriceMainPropUpdate] {$totalTimePriceMainUpdate} sec");
                }

                //* Номиналы цен *//

                $startTimeSetPrices = microtime(true);

                foreach ($productPrices->prices as $price) {
                    Job::dump('---');

                    //$shopTypePrice = $productPrices->ensureShopTypePrice($price['TypeGuid']);
//                    $shopTypePrice = ShopTypePrice::getShopTypePriceByGuid($price['TypeGuid'], true);
                    $shopTypePrice = ShopTypePriceModel::getPriceTypeMainDataByGuid($price['TypeGuid'], true);
                    if ($shopTypePrice) {

                        //Будем сохранять номиналы всегда нужных типов цен и текущую цену
                        if (
                            (!empty(ShopTypePriceModel::$savePriceTypes) && in_array($shopTypePrice['id'], ShopTypePriceModel::$savePriceTypes))
                            || ($shopTypePrice['id'] == $priceTypeMain['id'])
                        ) {
                        } else {
                            Job::dump("PriceType [{$shopTypePrice['id']}] {$shopTypePrice['name']} = {$price['PriceLoc']}");
                            Job::dump("Not important price type. SKIP SAVE.");
                            continue;
                        }

                        $startTimePrepareUpdatePrice = microtime(true);

//                        $shopProductPrice = ShopProductPrice::find()->andWhere(['type_price_id' => $shopTypePrice['id'], 'product_id' => $product->id])->one();
                        $shopProductPrice = \common\models\ShopProductPrice::find()->andWhere(['type_price_id' => $shopTypePrice['id'], 'product_id' => $product->id])->one();
                        if (empty($shopProductPrice)) {
                            $shopProductPrice = new \common\models\ShopProductPrice();
                            $shopProductPrice->type_price_id = $shopTypePrice['id'];
                            $shopProductPrice->product_id = $product->id;
                        }

                        $totalTimePrepareUpdatePrice = round((microtime(true) - $startTimePrepareUpdatePrice), 5);

                        Job::dump(" :time[prepareUpdatePrice {$shopTypePrice['id']}] {$totalTimePrepareUpdatePrice} sec");

                        //Может пустую цену тоже сохранять? Бывают их обнуляют
                        if ($price['PriceLoc']) {
                            $startTimeSavePrice = microtime(true);

                            //* Элемент списка цен *//
                            $shopProductPrice->price = $price['PriceLoc'];
                            if (!$shopProductPrice->save()) {
                                Job::dump('PriceSaveFailed: ' . $shopProductPrice->getErrors());
                                Yii::error('Failed save shop product price, errors ' . print_r($shopProductPrice->getErrors(), true), 'queue.price');
                            } else {
                                Job::dump("SAVED! PriceType [{$shopTypePrice['id']}] {$shopTypePrice['name']} = {$shopProductPrice->price}");
                            }
                            //* /Элемент списка цен *//

                            $totalTimeSavePrice = round((microtime(true) - $startTimeSavePrice), 5);

                            Job::dump(" :time[savePrice] {$totalTimeSavePrice} sec");
                        } else {
                            Job::dump('Price val is empty. Skip save.');
                        }
                    } else {
                        Job::dump("ERROR! Can't get (or create and get) price_type for guid '{$price['TypeGuid']}'");
                    }
                }

                $totalTimeSetPrices = round((microtime(true) - $startTimeSetPrices), 5);

                Job::dump(" :time[setPricesFull] {$totalTimeSetPrices} sec");

                //* /Номиналы цен *//

                //* Цена самого товара *//

                CommonHelper::startTimer('set_product_price_values');

                //Для более оперативного вступления в силу цен хотя бы на модификации, что бы уже продавать по правильной цене

                //Всегда используем только цену сайта 1 и 2 для выставления текущей и зачеркнутой цены

                $priceSite1 = 0;
                if (!empty($pricesByGuid[$priceTypeSite1['guid']]) && $pricesByGuid[$priceTypeSite1['guid']]['PriceLoc'] > 0) {
                    $priceSite1 = $pricesByGuid[$priceTypeSite1['guid']]['PriceLoc'];
                }

                $priceSite2 = 0;
                if (!empty($pricesByGuid[$priceTypeSite2['guid']]) && $pricesByGuid[$priceTypeSite2['guid']]['PriceLoc'] > 0) {
                    $priceSite2 = $pricesByGuid[$priceTypeSite2['guid']]['PriceLoc'];
                }

                //Достаточно всего лишь  Цены сайта 1 что бы поставить хоть чтото
                if ($priceSite1) {

                    Job::dump("---------");
                    Job::dump("Обновляю цены товара.");

                    //В новых реалиях нас интересует только соотношение цены сайта 1 и 2, остальное не используется
                    //Если цена сайта 2 определена и она меньше цены сайта 1 - то у нас кейс со скидкой
                    //Иначе - без скидок
                    if ($priceSite2 && $priceSite2 < $priceSite1){
                        $product->new_price = $priceSite2;
                        $product->new_price_old = $priceSite1;
                        $product->new_discount_percent = max(0, round((($product->new_price_old - $product->new_price) / $product->new_price_old) * 100));
                    }else{
                        $product->new_price = $priceSite1;
                        $product->new_price_old = $priceSite1;
                        $product->new_discount_percent = 0;
                    }

                    Job::dump("price = {$product->new_price}");
                    Job::dump("priceOld = {$product->new_price_old}");
                    Job::dump("discount = {$product->new_discount_percent}%");
                }

                //* /Цена самого товара *//

                //Все что нужно с товаром сделали, можно и сохранять
                if (!$product->save()) {
                    Job::dump("Update prices. Error save offer! Error: " . var_export($product->getErrors(), true));
                } else {
                    Job::dump("Product saved.");

                    //* Сохранение в карточку *//

                    //Для более оперативной актуализации цен в карточках можно сразу записывать цены и в них,
                    //так как для товаров с не отличающимися ценами модификаций это будет правильная цена

                    //Если цены модификаций отличаются, то можно получить неправильную цену в бОльшую сторону,
                    //но это не особо кретично так как следом пойдет пересчет карточек и цена исправится

                    if ($product->active == CommonHelper::BOOL_Y && $product->new_quantity > 0){
                        CommonHelper::startTimer('updatePriceCard');

                        \common\models\Product::updateAll(
                            [
                                'new_price_active' => $product->new_price_active,
                                'new_price' => $product->new_price,
                                'new_price_old' => $product->new_price_old,
                                'new_discount_percent' => $product->new_discount_percent,
                            ],
                            ['id' => $product->parent_content_element_id]
                        );

                        Job::dump("Обновление карточки ID={$product->parent_content_element_id}");

                        Job::dump(CommonHelper::getTimerTime('updatePriceCard'));
                    }

                    //* /Сохранение в карточку *//
                }

                Job::dump(CommonHelper::getTimerTime('set_product_price_values'));

                //Пересчет цен
                $startTimePush = microtime(true);

                //Получилось все вычислить прямо тут, так что запускать пересчет модификации смысла нет
                if (false) {
                    Yii::$app->queue->push(new UpdatePriceJob([
                        'id' => $product->id,
                    ]));
                }

                if (true) {
                    Yii::$app->queue->push(new UpdatePriceCardJob([
                        'id' => $product->parent_content_element_id,
                    ]));
                }

                $totalTimePush = round((microtime(true) - $startTimePush), 5);
                Job::dump(" :time[recalculatePush] {$totalTimePush} sec");
            } else {
                Job::dump("[SetPrices] KFSS source. Element type MOD is required, other given (productId={$product->id} | content_id={$product->content_id}). Skip processing.");
            }
        }

        //****** /ОБНОВЛЕНИЕ ТЕКУЩЕГО ЭЛЕМЕНТА (тип цены и номиналы) ******//

        //****** ОБНОВЛЕНИЕ ТИПА ЦЕНЫ ******//

        //Устанавливать тип цены имеет смысл только если он опреден
        //НЕ СОХРАНЯЕМ ИЗМЕНЕНИЕ ТАК КАК ТИП ЦЕНЫ ПЕРЕДЕЛАН НА РАСЧЕТНО-АНАЛИТИЧЕСКИЙ!
        if (false && $info['Source'] == 'KFSS' && $priceTypeMain) {
            if ($product->isLot()) {
                CommonHelper::startTimer('globalSetPriceType');

                Job::dump(">> Глобально устанавливаю тип цены");

                $product->new_price_active = $priceTypeMain['id'];
                $product->save(['new_price_active']);

                $cards = \common\models\Product::find()->onlyCard()->byParent($product->id);

//                foreach ($product->childrenContentElements as $card) {
                /** @var \common\models\Product $card */
                foreach ($cards->each() as $card) {

                    $offers = \common\models\Product::find()->onlyModification()->byParent($card->id);

                    /** @var \common\models\Product $offer */
//                    foreach ($card->childrenContentElements as $offer) {
                    foreach ($offers->each() as $offer) {
                        $offer->new_price_active = $priceTypeMain['id'];

                        if ($offer->save(false)) {
                            //Пересчет цен
                            Yii::$app->queue->push(new UpdatePriceJob([
                                'id' => $offer->id,
                            ]));
                        } else {
                            //error
                        }
                    }
                }

                Job::dump(CommonHelper::getTimerTime('globalSetPriceType'));
            } else {
                Job::dump("[SetPriceType] KFSS source. Element type LOT is required, other given. Skip processing.");
            }
        }

        //****** /ОБНОВЛЕНИЕ ТИПА ЦЕНЫ ******//

        Job::dump(CommonHelper::getTimerTime('GLOBAL'));

        return true;
    }

    //Прием
    //Вариант учитывающий указания по текущему типу цены
    protected function addPriceNew()
    {
        $info = $this->data['Info'];
        $data = $this->data['Data'];

        CommonHelper::startTimer('GLOBAL');

        $guid = trim($data['OffcntGuid']);

        Job::dump('---ProductPrice----');
        Job::dump('Guid: ' . $guid);
        Job::dump('Price count: ' . sizeof($data['Price']));
        Job::dump('PricesVary: ' . ($data['PricesVary'] ? 'Y' : 'N'));
        Job::dump('PriceMainGuid: ' . ($data['PriceMainGuid'] ?: '<<EMPTY>>'));

        $startTimePriceList = microtime(true);

        $productPrices = new PricesList();

        $product = $productPrices->getOrCreateElement($guid);
        if ($product == false) {
            Job::dump(' failed to get product');
            return false;
        }


        $productPrices->setCmsContentElement($product);
        $productPrices->setPricesList((array)$data['Price']);

        $productPrices->priceMainGuid = $data['PriceMainGuid'];
        $productPrices->pricesVary = $data['PricesVary'];

        $pricesByGuid = $productPrices->prices ? ArrayHelper::index($productPrices->prices, 'TypeGuid') : [];
        $priceTypeBase = ShopTypePriceModel::getPriceTypeMainDataById(ProductHelper::BASE_PRICE_ID);
        $priceTypeBaseOld = ShopTypePriceModel::getPriceTypeMainDataById(ProductHelper::BASE_PRICE_ID_OLD);

        //Нам в любом случае пригодится текущий тип цены

        $startTimePriceMainGet = microtime(true);

        //ТИПА ЦЕНЫ МОЖЕТ И НЕ БЫТЬ!

        //* ТИП ЦЕНЫ *//

        $priceTypeMain = false;
        if (!empty($productPrices->priceMainGuid)) {
            //$shopTypePrice = $productPrices->ensureShopTypePrice($productPrices->priceMainGuid);
            //$shopTypePriceMain = ShopTypePrice::getShopTypePriceByGuid($productPrices->priceMainGuid, true);
            $priceTypeMain = ShopTypePriceModel::getPriceTypeMainDataByGuid($productPrices->priceMainGuid, true);
            Job::dump("PriceMainIs: [{$priceTypeMain['id']}] {$priceTypeMain['name']}");

            if ($priceTypeMain) {

                if ($priceTypeMain['id'] == ProductHelper::BASE_PRICE_ID_OLD) {
                    Job::dump("PriceMain is OLD! Correcting...");//$shopTypePriceMainProper = ShopTypePrice::findOne(\common\helpers\Product::BASE_PRICE_ID);
                    if ($priceTypeBase) {
                        $priceTypeMain = $priceTypeBase;
                        Job::dump("PriceMainPROPER Is: [{$priceTypeMain['id']}] {$priceTypeMain['name']}");
                    } else {
                        Job::dump("Не могу найти новый базовый тип цены! Использую старый.");
                    }
                }

            } else {
                Job::dump("Не могу найти базовый тип цены! GUID: '{$productPrices->priceMainGuid}'");
            }

            $totalTimePriceMainGet = round((microtime(true) - $startTimePriceMainGet), 5);
            Job::dump(" :time[PriceMainGet] {$totalTimePriceMainGet} sec");
        } else {
            Job::dump("PriceMainIs: EMPTY!");
            //Отсутствие типа цены не такая уж рекость, так что это нюанс, а не ошибка
            //\Yii::error("PriceMainIs EMPTY! Product GUID='{$guid}'", "");
        }

        //* /ТИП ЦЕНЫ *//


        $totalTimePriceList = round((microtime(true) - $startTimePriceList), 5);

        Job::dump(" :time[Prepare] = {$totalTimePriceList} sec");

        //****** ОБНОВЛЕНИЕ ТЕКУЩЕГО ЭЛЕМЕНТА (тип цены и номиналы) ******//

        //1С пропускаем теперь всегда, всем заведует КФСС
        if ($info['Source'] == '1C') {
            Job::dump("1C source. Skip processing.");
        }

        if ($info['Source'] == 'KFSS') { //Если что то не так - вернуть == '1C'
            if ($product->isOffer()) {

                if (!empty($priceTypeMain)) {
                    $startTimePriceMainUpdate = microtime(true);
                    //$this->updatePriceActive($product, $shopTypePriceMain);
                    //Тип цены изменился
                    if ($priceTypeMain && $product->new_price_active != $priceTypeMain['id']) {
                        $product->new_price_active = $priceTypeMain['id'];
                    }

                    //Сохранение в этом месте преждевременно, так что таймер не особо актуален, но пока оставим
                    $totalTimePriceMainUpdate = round((microtime(true) - $startTimePriceMainUpdate), 5);
                    Job::dump(" :time[PriceMainPropUpdate] {$totalTimePriceMainUpdate} sec");
                }

                //* Номиналы цен *//

                $startTimeSetPrices = microtime(true);

                foreach ($productPrices->prices as $price) {
                    Job::dump('---');

                    //$shopTypePrice = $productPrices->ensureShopTypePrice($price['TypeGuid']);
//                    $shopTypePrice = ShopTypePrice::getShopTypePriceByGuid($price['TypeGuid'], true);
                    $shopTypePrice = ShopTypePriceModel::getPriceTypeMainDataByGuid($price['TypeGuid'], true);
                    if ($shopTypePrice) {

                        //Будем сохранять номиналы всегда нужных типов цен и текущую цену
                        if (
                            (!empty(ShopTypePriceModel::$savePriceTypes) && in_array($shopTypePrice['id'], ShopTypePriceModel::$savePriceTypes))
                            || ($shopTypePrice['id'] == $priceTypeMain['id'])
                        ) {
                        } else {
                            Job::dump("PriceType [{$shopTypePrice['id']}] {$shopTypePrice['name']} = {$price['PriceLoc']}");
                            Job::dump("Not important price type. SKIP SAVE.");
                            continue;
                        }

                        $startTimePrepareUpdatePrice = microtime(true);

//                        $shopProductPrice = ShopProductPrice::find()->andWhere(['type_price_id' => $shopTypePrice['id'], 'product_id' => $product->id])->one();
                        $shopProductPrice = \common\models\ShopProductPrice::find()->andWhere(['type_price_id' => $shopTypePrice['id'], 'product_id' => $product->id])->one();
                        if (empty($shopProductPrice)) {
                            $shopProductPrice = new \common\models\ShopProductPrice();
                            $shopProductPrice->type_price_id = $shopTypePrice['id'];
                            $shopProductPrice->product_id = $product->id;
                        }

                        $totalTimePrepareUpdatePrice = round((microtime(true) - $startTimePrepareUpdatePrice), 5);

                        Job::dump(" :time[prepareUpdatePrice {$shopTypePrice['id']}] {$totalTimePrepareUpdatePrice} sec");

                        //Может пустую цену тоже сохранять? Бывают их обнуляют
                        if ($price['PriceLoc']) {
                            $startTimeSavePrice = microtime(true);

                            //* Элемент списка цен *//
                            $shopProductPrice->price = $price['PriceLoc'];
                            if (!$shopProductPrice->save()) {
                                Job::dump('PriceSaveFailed: ' . $shopProductPrice->getErrors());
                                Yii::error('Failed save shop product price, errors ' . print_r($shopProductPrice->getErrors(), true), 'queue.price');
                            } else {
                                Job::dump("SAVED! PriceType [{$shopTypePrice['id']}] {$shopTypePrice['name']} = {$shopProductPrice->price}");
                            }
                            //* /Элемент списка цен *//

                            $totalTimeSavePrice = round((microtime(true) - $startTimeSavePrice), 5);

                            Job::dump(" :time[savePrice] {$totalTimeSavePrice} sec");
                        } else {
                            Job::dump('Price val is empty. Skip save.');
                        }
                    } else {
                        Job::dump("ERROR! Can't get (or create and get) price_type for guid '{$price['TypeGuid']}'");
                    }
                }

                $totalTimeSetPrices = round((microtime(true) - $startTimeSetPrices), 5);

                Job::dump(" :time[setPricesFull] {$totalTimeSetPrices} sec");

                //* /Номиналы цен *//

                //* Цена самого товара *//

                CommonHelper::startTimer('set_product_price_values');

                //Для более оперативного вступления в силу цен хотя бы на модификации, что бы уже продавать по правильной цене

                //Если цена базовая, то сразу понятно какие будут основная и зачеркнутая цена (одинаково базовые), а так же процент скидки
                //Если цена не базовая, то необходимо понять что ставить в зачеркнутую цену
                //- Если тип цены Сайта2 - то ставим в приоритете ЦенаСайта1, потом Базовая
                //- Если тип цены НЕ Сайта2 - ставим в обратном приоритете, Базовая, потом Сайта1. Это подстрахует для эфирных лотов.
                //Дальнейший пересчет проверит эфирность и выставит правильную цену

                if ($priceTypeMain) {

                    Job::dump("---------");
                    Job::dump("Текущий тип цены указан. Обновляю цены товара.");

                    //Текущая цена. Должна быть в списке цен и не должна быть нулевой
                    if (!empty($pricesByGuid[$priceTypeMain['guid']]) && $pricesByGuid[$priceTypeMain['guid']]['PriceLoc']) {
                        $product->new_price = $pricesByGuid[$priceTypeMain['guid']]['PriceLoc'];
                    }

                    //* ВЫЯСНЯЕМ ЧТО ДЕЛАТЬ С ЗАЧЕРКНУТОЙ ЦЕНОЙ *//

                    CommonHelper::startTimer('get_price_old');

                    //* Цена базовая текущая *//

                    //Тут все просто и поянтно, обе цены одинаковые и скидки нет
                    if ($priceTypeMain['id'] == ProductHelper::BASE_PRICE_ID || $priceTypeMain['id'] == ProductHelper::BASE_PRICE_ID_OLD) {
                        $product->new_price_old = $product->new_price;
                        $product->new_discount_percent = 0;
                    }

                    //* /Цена базовая *//

                    //* Цена НЕ базовая *//

                    if ($priceTypeMain['id'] != ProductHelper::BASE_PRICE_ID) {

                        $priceBase = 0;
                        if ($priceTypeBase && !empty($pricesByGuid[$priceTypeBase['guid']]) && $pricesByGuid[$priceTypeBase['guid']]['PriceLoc'] > 0) {
                            $priceBase = $pricesByGuid[$priceTypeBase['guid']]['PriceLoc'];
                        }

                        $priceBaseOld = 0;
                        if ($priceTypeBaseOld && !empty($pricesByGuid[$priceTypeBaseOld['guid']]) && $pricesByGuid[$priceTypeBaseOld['guid']]['PriceLoc'] > 0) {
                            $priceBaseOld = $pricesByGuid[$priceTypeBaseOld['guid']]['PriceLoc'];
                        }

                        //Если товар в эфире берем старую базовую цену
                        //Если нет - то пробуем взять новую базовую (Сайта1), если ее нету - тогда старую базовую
                        CommonHelper::startTimer('getOnAirCards');
                        $cardsOnAir = SsMediaplanAirDayProductTime::getTodayAirProductsCardsIds(true);
                        Job::dump(CommonHelper::getTimerTime('getOnAirCards'));

                        $isOnAir = (bool)($cardsOnAir && isset($cardsOnAir[$product->parent_content_element_id]));
                        $product->new_price_old = $isOnAir ? $priceBaseOld : $priceBase;

                        $product->new_discount_percent = 0;
                        if ($product->new_price > 0 && $product->new_price_active > 0 && $product->new_price < $product->new_price_old) {
                            $product->new_discount_percent = max(0, round((($product->new_price_old - $product->new_price) / $product->new_price_old) * 100));
                        }
                    }

                    //* /Цена НЕ базовая *//

                    Job::dump(CommonHelper::getTimerTime('get_price_old'));

                    //* /ВЫЯСНЯЕМ ЧТО ДЕЛАТЬ С ЗАЧЕРКНУТОЙ ЦЕНОЙ *//

                    if (isset($isOnAir)){
                        Job::dump("isOnAir = " . ($isOnAir ? 'Y' : 'N'));
                    }
                    Job::dump("price = {$product->new_price}");
                    Job::dump("priceOld = {$product->new_price_old}");
                    Job::dump("discount = {$product->new_discount_percent}%");
                }

                //* /Цена самого товара *//

                //Все что нужно с товаром сделали, можно и сохранять
                if (!$product->save()) {
                    Job::dump("Update prices. Error save offer! Error: " . var_export($product->getErrors(), true));
                } else {
                    Job::dump("Product saved.");

                    //* Сохранение в карточку *//

                    //Для более оперативной актуализации цен в карточках можно сразу записывать цены и в них,
                    //так как для товаров с не отличающимися ценами модификаций это будет правильная цена

                    //Если цены модификаций отличаются, то можно получить неправильную цену в бОльшую сторону,
                    //но это не особо кретично так как следом пойдет пересчет карточек и цена исправится

                    if ($product->active == CommonHelper::BOOL_Y && $product->new_quantity > 0){
                        CommonHelper::startTimer('updatePriceCard');

                        \common\models\Product::updateAll(
                            [
                                'new_price_active' => $product->new_price_active,
                                'new_price' => $product->new_price,
                                'new_price_old' => $product->new_price_old,
                                'new_discount_percent' => $product->new_discount_percent,
                            ],
                            ['id' => $product->parent_content_element_id]
                        );

                        Job::dump("Обновление карточки ID={$product->parent_content_element_id}");

                        Job::dump(CommonHelper::getTimerTime('updatePriceCard'));
                    }

                    //* /Сохранение в карточку *//
                }

                Job::dump(CommonHelper::getTimerTime('set_product_price_values'));

                //Пересчет цен
                $startTimePush = microtime(true);

                //Получилось все вычислить прямо тут, так что запускать пересчет модификации смысла нет
                if (false) {
                    Yii::$app->queue->push(new UpdatePriceJob([
                        'id' => $product->id,
                    ]));
                }

                if (true) {
                    Yii::$app->queue->push(new UpdatePriceCardJob([
                        'id' => $product->parent_content_element_id,
                    ]));
                }

                $totalTimePush = round((microtime(true) - $startTimePush), 5);
                Job::dump(" :time[recalculatePush] {$totalTimePush} sec");
            } else {
                Job::dump("[SetPrices] KFSS source. Element type MOD is required, other given (productId={$product->id} | content_id={$product->content_id}). Skip processing.");
            }
        }

        //****** /ОБНОВЛЕНИЕ ТЕКУЩЕГО ЭЛЕМЕНТА (тип цены и номиналы) ******//

        //****** ОБНОВЛЕНИЕ ТИПА ЦЕНЫ ******//

        //Устанавливать тип цены имеет смысл только если он опреден
        if ($info['Source'] == 'KFSS' && $priceTypeMain) {
            if ($product->isLot()) {
                CommonHelper::startTimer('globalSetPriceType');

                Job::dump(">> Глобально устанавливаю тип цены");

                $product->new_price_active = $priceTypeMain['id'];
                $product->save(['new_price_active']);

                $cards = \common\models\Product::find()->onlyCard()->byParent($product->id);

//                foreach ($product->childrenContentElements as $card) {
                /** @var \common\models\Product $card */
                foreach ($cards->each() as $card) {

                    $offers = \common\models\Product::find()->onlyModification()->byParent($card->id);

                    /** @var \common\models\Product $offer */
//                    foreach ($card->childrenContentElements as $offer) {
                    foreach ($offers->each() as $offer) {
                        $offer->new_price_active = $priceTypeMain['id'];

                        if ($offer->save(false)) {
                            //Пересчет цен
                            Yii::$app->queue->push(new UpdatePriceJob([
                                'id' => $offer->id,
                            ]));
                        } else {
                            //error
                        }
                    }
                }

                Job::dump(CommonHelper::getTimerTime('globalSetPriceType'));
            } else {
                Job::dump("[SetPriceType] KFSS source. Element type LOT is required, other given. Skip processing.");
            }
        }

        //****** /ОБНОВЛЕНИЕ ТИПА ЦЕНЫ ******//

        Job::dump(CommonHelper::getTimerTime('GLOBAL'));

        return true;
    }

    /**
     * @return bool
     */
    protected function addPrice()
    {
        $info = $this->data['Info'];
        $data = $this->data['Data'];


        $guid = trim($data['OffcntGuid']);

        Job::dump('---ProductPrice----');
        Job::dump('Guid: ' . $guid);
        Job::dump('Price count: ' . sizeof($data['Price']));
        Job::dump('PricesVary: ' . ($data['PricesVary'] ? 'Y' : 'N'));
        Job::dump('PriceMainGuid: ' . $data['PriceMainGuid']);

        // цен нет, нечего обрабатывать
//        if (sizeof($data['Price']) == 0) {
//            return true;
//        }

        $startTimePriceList = microtime(true);

        $productPrices = new PricesList();

        $product = $productPrices->getOrCreateElement($guid);
        if ($product == false) {
            Job::dump(' failed to get product');
            return false;
        }


        $productPrices->setCmsContentElement($product);
        $productPrices->setPricesList((array)$data['Price']);

        $productPrices->priceMainGuid = $data['PriceMainGuid'];
        $productPrices->pricesVary = $data['PricesVary'];

        $totalTimePriceList = round((microtime(true) - $startTimePriceList), 5);

        Job::dump(" :time[Prepare] = {$totalTimePriceList} sec");

        // New functional save price and price type

        //1С пропускаем теперь всегда, всем заведует КФСС
        if ($info['Source'] == '1C') {
            Job::dump("1C source. Skip processing.");
        }

        if ($info['Source'] == 'KFSS') { //Если что то не так - вернуть == '1C'
            if ($product->isOffer()) {

                $startTimePriceMainGet = microtime(true);

                //ТИПА ЦЕНЫ МОЖЕТ И НЕ БЫТЬ!

                if (!empty($productPrices->priceMainGuid)) {
                    //$shopTypePrice = $productPrices->ensureShopTypePrice($productPrices->priceMainGuid);
                    $shopTypePriceMain = ShopTypePrice::getShopTypePriceByGuid($productPrices->priceMainGuid, true);
                    Job::dump("PriceMainIs: [{$shopTypePriceMain->id}] {$shopTypePriceMain->name}");

                    if ($shopTypePriceMain->id == ProductHelper::BASE_PRICE_ID_OLD) {
                        Job::dump("PriceMain is OLD! Correcting...");
                        $shopTypePriceMainProper = ShopTypePrice::findOne(ProductHelper::BASE_PRICE_ID);

                        if ($shopTypePriceMainProper) {
                            $shopTypePriceMain = $shopTypePriceMainProper;
                            Job::dump("PriceMainPROPER Is: [{$shopTypePriceMain->id}] {$shopTypePriceMain->name}");
                        } else {
                            Job::dump("Не могу найти новый базовый тип цены! Использую старый.");
                        }
                    }

                    $totalTimePriceMainGet = round((microtime(true) - $startTimePriceMainGet), 5);
                    Job::dump(" :time[PriceMainGet] {$totalTimePriceMainGet} sec");

                    $startTimePriceMainUpdate = microtime(true);
                    $this->updatePriceActive($product, $shopTypePriceMain);
                    $totalTimePriceMainUpdate = round((microtime(true) - $startTimePriceMainUpdate), 5);
                    Job::dump(" :time[PriceMainPropUpdate] {$totalTimePriceMainUpdate} sec");
                } else {
                    Job::dump("PriceMainIs: EMPTY!");
                    //Отсутствие типа цены не такая уж рекость, так что это нюанс, а не ошибка
                    //\Yii::error("PriceMainIs EMPTY! Product GUID='{$guid}'", "");
                }

                $startTimeSetPrices = microtime(true);

                foreach ($productPrices->prices as $price) {
                    Job::dump('---');

                    //$shopTypePrice = $productPrices->ensureShopTypePrice($price['TypeGuid']);
                    $shopTypePrice = ShopTypePrice::getShopTypePriceByGuid($price['TypeGuid'], true);
                    if ($shopTypePrice) {

                        $startTimePrepareUpdatePrice = microtime(true);

                        $shopProductPrice = ShopProductPrice::find()->andWhere(['type_price_id' => $shopTypePrice->id, 'product_id' => $product->id])->one();
                        if (empty($shopProductPrice)) {
                            $shopProductPrice = new ShopProductPrice();
                            $shopProductPrice->type_price_id = $shopTypePrice->id;
                            $shopProductPrice->product_id = $product->id;
                        }

                        $totalTimePrepareUpdatePrice = round((microtime(true) - $startTimePrepareUpdatePrice), 5);

                        Job::dump(" :time[prepareUpdatePrice] {$totalTimePrepareUpdatePrice} sec");

                        if ($price['PriceLoc']) {
                            $startTimeSavePrice = microtime(true);

                            $shopProductPrice->price = $price['PriceLoc'];
                            if (!$shopProductPrice->save()) {
                                Job::dump('PriceSaveFailed: ' . $shopProductPrice->getErrors());
                                Yii::error('Failed save shop product price, errors ' . print_r($shopProductPrice->getErrors(), true), 'queue.price');
                            } else {
                                Job::dump("SAVED! PriceType [{$shopTypePrice->id}] {$shopTypePrice->name} = {$shopProductPrice->price}");
                            }

                            $totalTimeSavePrice = round((microtime(true) - $startTimeSavePrice), 5);

                            Job::dump(" :time[savePrice] {$totalTimeSavePrice} sec");
                        } else {
                            Job::dump('Price val is empty. Skip save.');
                        }
                    } else {
                        Job::dump("ERROR! Can't get (or create and get) price_type for guid '{$price['TypeGuid']}'");
                    }
                }

                $totalTimeSetPrices = round((microtime(true) - $startTimeSetPrices), 5);

                Job::dump(" :time[setPricesFull] {$totalTimeSetPrices} sec");

                //Пересчет цен
                $startTimePush = microtime(true);
                Yii::$app->queue->push(new UpdatePriceJob([
                    'id' => $product->id,
                ]));
                $totalTimePush = round((microtime(true) - $startTimePush), 5);
                Job::dump(" :time[recalculatePush] {$totalTimePush} sec");
            } else {
                Job::dump("KFSS source. Element type MOD is required, other given (productId={$product->id} | content_id={$product->content_id}). Skip processing.");
            }
        }

        //Устанавливать тип цены имеет смысл только если он опреден
        if (/*$info['Source'] == 'Bitrix' ||*/ $info['Source'] == 'KFSS' && $productPrices->priceMainGuid) {
//            $shopTypePrice = $productPrices->ensureShopTypePrice($productPrices->priceMainGuid);
            $shopTypePrice = ShopTypePrice::getShopTypePriceByGuid($productPrices->priceMainGuid, true);

            if ($shopTypePrice->id == ProductHelper::BASE_PRICE_ID_OLD) {
                Job::dump("PriceMain is OLD! Correcting...");
                $shopTypePriceProper = ShopTypePrice::findOne(ProductHelper::BASE_PRICE_ID);

                if ($shopTypePriceProper) {
                    $shopTypePrice = $shopTypePriceProper;
                    Job::dump("PriceMainPROPER Is: [{$shopTypePrice->id}] {$shopTypePrice->name}");
                } else {
                    Job::dump("Не могу найти новый базовый тип цены! Использую старый.");
                }
            }

            if ($product->isLot()) {
                if ($shopTypePrice) {
                    $this->updatePriceActive($product, $shopTypePrice);
                    foreach ($product->childrenContentElements as $card) {
//                        Job::dump('Foreach $card : ' . $card->id);
                        foreach ($card->childrenContentElements as $offer) {
//                            Job::dump('Foreach $offer : ' . $offer->id);
                            if ($this->updatePriceActive($offer, $shopTypePrice)) {
//                                Job::dump('Foreach  updatePriceActive $offer : ' . $offer->id);
                                //Пересчет цен
                                Yii::$app->queue->push(new UpdatePriceJob([
                                    'id' => $offer->id,
                                ]));
                            }
                        }
                    }
                } else {
                    Job::dump("ERROR! Can't get (or create and get) price_type for guid '{$productPrices->priceMainGuid}'");
                }
            } else {
                Job::dump("KFSS source. Element type LOT is required, other given. Skip processing.");
            }
        }


        return true;


//        return $productPrices->addData();

    }

    /**
     * @param $element CmsContentElement
     * @param $shopTypePrice
     * @return bool
     */
    public function updatePriceActive($element, $shopTypePrice)
    {
        $priceActiveProperty = CmsContentProperty::findOne(['code' => 'PRICE_ACTIVE', 'content_id' => $element->content_id]);
        if ($priceActiveProperty) {
            Job::dump('---');
            Job::dump("ElementId = {$element->id} [{$element->content_id}] | Set PriceActive Prop = [{$shopTypePrice->id}] {$shopTypePrice->name}");

            $priceActive = (string)$shopTypePrice->id;

            $elementPropertyPriceActive = CmsContentElementProperty::findOne([
                'property_id' => $priceActiveProperty->id,
                'element_id' => $element->id
            ]);

            if (!$elementPropertyPriceActive) {
                $elementPropertyPriceActive = new CmsContentElementProperty(['property_id' => $priceActiveProperty->id, 'element_id' => $element->id]);
            }

            $elementPropertyPriceActive->value = $priceActive;

            if ($elementPropertyPriceActive->isAttributeChanged('value')) {
                if (!$elementPropertyPriceActive->save()) {
                    Yii::error('Failed update active price, errors ' . print_r($elementPropertyPriceActive->getErrors(), true), 'queue.price');
                    Job::dump('PriceActiveSaveFailed: ' . $elementPropertyPriceActive->getErrors());
                    return false;
                } else {
                    Job::dump("Saved!");
                }
            } else {
                Job::dump("PriceActive Prop not changed. Skip update.");
            }
        } else {
            Job::dump("ERROR! Can't Find PRICE_ACTIVE prop for content_id={$element->content_id}");
        }

        return true;
    }
}