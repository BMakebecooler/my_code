<?php
namespace modules\shopandshow\models\newEntities\products;

use common\helpers\ArrayHelper;
use common\models\cmsContent\CmsContentElement;
use common\models\cmsContent\CmsContentProperty;
use common\models\ProductProperty;
use console\controllers\queues\jobs\Job;
use console\jobs\UpdatePriceJob;
use http\Exception;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\newEntities\common\CmsContentElementModel;
use modules\shopandshow\models\shop\ShopTypePrice;
use skeeks\cms\models\CmsContentElementProperty;
use skeeks\cms\shop\models\ShopProductPrice;
use Yii;

class PricesList extends CmsContentElementModel
{
    public $priceMainGuid;
    public $pricesVary = false;

    public $prices = [];
    private $childs = [];

    //Массив для сбора пачки цен для обновления
    private $pricesForUpdate = [];

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'guid' => 'Глобальный идентификатор',
        ];
    }

    public function setCmsContentElement(CmsContentElement $contentElement)
    {
        $this->cmsContentElement = $contentElement;

        $this->setAttributes([
            'guid' => $contentElement->guid->getGuid(),
        ]);
    }

    public function setPricesList(array $pricesList = [])
    {
        $this->prices = $pricesList;
    }

    /**
     * @return bool
     */
    public function addData()
    {
        $startTime = microtime(true);
//        Job::dump('ProductId: ' . $this->cmsContentElement->id);

        //Тип цены и активную цену сохраним в любом случае.

        //Работа с relatedProps очень долгая, заменяем чере работу сразу с БД

        if (false) {
            //$this->relatedPropertiesModel['PRICES_VARY'] = (string)intval($this->pricesVary);
            if ($this->priceMainGuid) {
                $shopTypePrice = $this->ensureShopTypePrice($this->priceMainGuid);
//                $this->relatedPropertiesModel['PRICE_ACTIVE'] = (string)$shopTypePrice->id;
                ProductProperty::savePropByCode($this->cmsContentElement->id, 'PRICE_ACTIVE', (string)$shopTypePrice->id);
            }

            //$this->saveRelatedProperties();
        }

        //* PRICES_VARY *//

        if (true && !$this->cmsContentElement->isCard()) {

            $pricesVaryProperty = CmsContentProperty::findOne(['code' => 'PRICES_VARY']);
            $pricesVaryPropertyId = $pricesVaryProperty->id ?: 0;

            if ($pricesVaryPropertyId) {
                $pricesVary = (string)intval($this->pricesVary);

                $elementPropertyPricesVary = CmsContentElementProperty::findOne(['property_id' => $pricesVaryPropertyId, 'element_id' => $this->cmsContentElement->id]);

                if (!$elementPropertyPricesVary) {
                    $elementPropertyPricesVary = new CmsContentElementProperty(['property_id' => $pricesVaryPropertyId, 'element_id' => $this->cmsContentElement->id]);
                }

                //Если свойство найдено, сравниваем значение. Если изменений нет - сохранять не имеет смысла.
                if (
                    $elementPropertyPricesVary->isNewRecord
                    || !isset($elementPropertyPricesVary->value)
                    || ($elementPropertyPricesVary->value != $pricesVary)
                ) {
                    $elementPropertyPricesVary->value = $pricesVary;
                    if (!$elementPropertyPricesVary->save()) {
                        Job::dump('PricesVarySaveFailed: ' . $elementPropertyPricesVary->getErrors());
                    }
                } else {
//                    Job::dump("Update unnecessarily");
                }
            }
        }

        //* /PRICES_VARY *//

        //* PRICE_ACTIVE *//

        //Сбоит, но пока не ясно почему
        if (true && $this->priceMainGuid && !$this->cmsContentElement->isCard()) {

            $priceActiveProperty = CmsContentProperty::findOne(['code' => 'PRICE_ACTIVE', 'content_id' => $this->cmsContentElement->content_id]);
            $priceActivePropertyId = $priceActiveProperty->id ?: 0;

            if ($priceActivePropertyId) {

                $shopTypePrice = $this->ensureShopTypePrice($this->priceMainGuid);
                $priceActive = (string)$shopTypePrice->id;

                $elementPropertyPriceActive = CmsContentElementProperty::findOne([
                    'property_id' => $priceActivePropertyId,
                    'element_id' => $this->cmsContentElement->id
                ]);

                if (!$elementPropertyPriceActive) {
                    $elementPropertyPriceActive = new CmsContentElementProperty(['property_id' => $priceActivePropertyId, 'element_id' => $this->cmsContentElement->id]);
                }

                //Если свойство найдено, сравниваем значение. Если изменений нет - сохранять не имеет смысла.
                if (
                    $elementPropertyPriceActive->isNewRecord
                    || !isset($elementPropertyPriceActive->value)
                    || ($elementPropertyPriceActive->value != $priceActive)
                ) {
                    $elementPropertyPriceActive->value = $priceActive;
                    if (!$elementPropertyPriceActive->save()) {
                        Job::dump('PriceActiveSaveFailed: ' . $elementPropertyPriceActive->getErrors());
                    }
                } else {
//                    var_dump("Update unnecessarily");
                }
            }
//                Job::dump("priceActive DONE.");
        }

        //* /PRICE_ACTIVE *//

        $totalTime = round((microtime(true) - $startTime), 5);
        Job::dump("> RuntimeStep saveRelProps (alt): {$totalTime} sec");

        // если цены не отличаются, а это модификация, то ничего не апдейтим
//        if ($this->checkOfferPricesVary()) {
//        if (!$this->pricesVary && $this->cmsContentElement->isOffer()) {
////            Job::dump("Modification, PricesVary=false. Update skip.");
//            return true;
//        }
//
//        //Если у нас лот и цены отличаются - пропускаем. Все будет через модификации.
        if ($this->pricesVary && $this->cmsContentElement->isLot()){
            return true;
        }

        $startTime = microtime(true);

        // собираем всех детей, у которых могут быть установлены цены
        $this->childs = $this->getChilds();
//        Job::dump(' ChildsNum - ' . count($this->childs));

        $totalTime = round((microtime(true) - $startTime), 5);
        Job::dump("> RuntimeStep getChilds: {$totalTime} sec");

        $this->pricesForUpdate = [];

        $startTime = microtime(true);

        foreach ($this->prices as $price) {
            Job::dump('TypeGuid: '.$price['TypeGuid']);
            Job::dump('PriceLoc: '.$price['PriceLoc']);

            // Если значения цены нет то и продолжать смысла нет
            $priceVal = str_replace(',', '.', $price['PriceLoc']);
            if (!(empty(trim($priceVal)) || !is_numeric($priceVal))) {

                if (!$shopTypePrice = $this->ensureShopTypePrice($price['TypeGuid'])) {
                    Job::dump(" Error: Can't get price type");
                    return false;
                }

                if (!$shopProduct = self::ensureShopProduct($this->cmsContentElement->id, [], true)) {
                    Job::dump(" Error: Can't get shopProduct");
                    return false;
                }

                if (!$productPrice = $this->updateProductPrice($shopTypePrice->id, $this->cmsContentElement->id, $price['PriceLoc'])) {
                    Job::dump(" Error: Can't update price");
                    return false;
                }

                // устанавливаем цены на модификации, если их еще нет
                if ($this->childs) {
//                Job::dump(" Update child prices");
                    foreach ($this->childs as $child) {
                        if (!$shopProduct = self::ensureShopProduct($child->id, [], true)) {
                            Job::dump(" Error: Can't get child shopProduct");
                            return false;
                        }

                        if (!$productPrice = $this->updateProductPrice($shopTypePrice->id, $child->id, $price['PriceLoc'], !$this->pricesVary)) {
                            Job::dump(" Error: Can't update child price");
                            return false;
                        }
                    }
                }
            }
        }

        $totalTime = round((microtime(true) - $startTime), 5);
        Job::dump("> RuntimeStep getPricesForUpdate: {$totalTime} sec");

        //Записываем накопленные обновления в БД
        if ($this->pricesForUpdate){
            $startTime = microtime(true);
            $queryUpdatePrice = <<<SQL
UPDATE shop_product_price SET price=:price WHERE id=:priceId
SQL;
            $price = 0;
            $priceId = 0;
            $commandUpdate = \Yii::$app->db->createCommand($queryUpdatePrice)
                ->bindParam(':price', $price)
                ->bindParam(':priceId', $priceId);

            $i = 0;
            foreach ($this->pricesForUpdate as $priceId => $price) {
                $i++;
                if($i<=10){
                    Job::dump("> [{$i}] {$priceId} => {$price}");
                }
                $commandUpdate->execute();
            }

            $totalTime = round((microtime(true) - $startTime), 5);
            Job::dump("> RuntimeStep UpdatePriceBlock: {$totalTime} sec [".count($this->pricesForUpdate)." items]");
        }



        return true;

        //return $this->priceIndex();
    }

    /**
     * @param $guid
     *
     * @return bool|ShopTypePrice
     */
    public function ensureShopTypePrice($guid)
    {
        static $shopTypePrices = [];

        if (!$shopTypePrices) {
            $shopTypePrices = ShopTypePrice::find()->innerJoinWith('guidRelation')->indexBy(function ($row) {return $row['guidRelation']->guid;})->all();
        }

        if (array_key_exists($guid, $shopTypePrices)) {
            $shopTypePrice = $shopTypePrices[$guid];
        }
        else {
            /** @var ShopTypePrice $shopTypePrice */
            $shopTypePrice = Guids::getEntityByGuid($guid);
        }

        if (!$shopTypePrice) {

            $shopTypePrice = new ShopTypePrice();

            $shopTypePrice->guid->setGuid($guid);
            $shopTypePrice->code = uniqid();
            $shopTypePrice->name = uniqid();

            if (!$shopTypePrice->save()) {
                Job::dump($shopTypePrice->getErrors());
                return false;
            }
        }

        return $shopTypePrice;
    }

    /**
     * @param int  $shopTypePriceId
     * @param int  $productId
     * @param      $price
     * @param bool $updateExists
     *
     * @return bool|ShopProductPrice
     */
    protected function updateProductPrice($shopTypePriceId, $productId, $price, $updateExists = true)
    {
        static $productPrices = [];

        if (empty($productPrices[$productId])) {
            $productPrices[$productId] = ShopProductPrice::find()->where(['product_id' => $productId])->indexBy('type_price_id')->all();
        }

        if (empty(trim($price))) {
            //Job::dump(' PriceEmpty:update skip');
            return true;
        }

        $price = str_replace(',', '.', $price);
        if (!is_numeric($price)) {
            Job::dump(' PriceNAN:update skip');
            return true;
        }
        if (isset($productPrices[$productId][$shopTypePriceId])) {
            $productPrice = $productPrices[$productId][$shopTypePriceId];
        }
        else {
            /**
             * @var $productPrice ShopProductPrice
             */
            $productPrice = ShopProductPrice::find()
                ->andWhere(
                    'product_id =:product_id AND type_price_id =:type_price_id',
                    [
                        ':product_id' => $productId,
                        ':type_price_id' => $shopTypePriceId,
                    ]
                )->one();
        }

        if (!$productPrice) {
            $productPrice = new ShopProductPrice();
            $productPrice->product_id = $productId;
            $productPrice->type_price_id = $shopTypePriceId;
            $productPrice->price = $price;

            Job::dump(" Set new price type '$shopTypePriceId' for product #$productId = {$productPrice->price}");
        }
        elseif ($updateExists) {
            $productPrice->price = $price;

//            Job::dump(" Update product={$productPrice->product_id} exist price #{$productPrice->id} [{$productPrice->type_price_id}] = {$productPrice->price}");
        }

        //Если у нас новая запись цены - сохраняем сразу
        //Eсли обновление существующей - записываем в пачку для комплексного обновления
        if ($productPrice->isNewRecord){
            if (!$productPrice->save()) {
                Job::dump(" FAIL Update price #{$productPrice->id}.");
                Job::dump($productPrice->getErrors());
                return false;
            }
        }else{
            $this->pricesForUpdate[$productPrice->id] = $price;
        }

        return $productPrice;
    }

    /**
     * @return array
     */
    protected function getChilds()
    {
        $result = [];

        //Если у нас не лот, то дети нам не нужны
        if ($this->cmsContentElement->isLot()){
            $childs = $this->cmsContentElement->childrenContentElements;
            // если есть дети
            if ($childs) {
                // добавляем модификации, если есть
                $result = array_merge($result, array_filter($childs, function ($child) {
                    return $child->content_id == OFFERS_CONTENT_ID;
                }));
                foreach ($childs as $child) {
                    // если есть еще дети
                    if ($child->childrenContentElements) {
                        $result = array_merge($result, array_filter($child->childrenContentElements, function ($child) {
                            return $child->content_id == OFFERS_CONTENT_ID;
                        }));
                    }
                }
            }
        }

        return $result;
    }

    /**
     * проверяет, отличаются ли цены модификаций
     * возвращает true, если это модификация, и цену ей ставить не надо
     */
    protected function checkOfferPricesVary()
    {
        // это лот - все ок, ставим цены
        if ($this->cmsContentElement->isLot()) {
            return false;
        }

        $pricesVary = 0;

        $parent = $this->cmsContentElement->product;
        // цены отличаются
        if ($parent->relatedPropertiesModel) {
            $pricesVary = (int)$parent->relatedPropertiesModel->getAttribute('PRICES_VARY');
        }

        // true = не отличаются
        return !$pricesVary;
    }

    /**
     * @deprecated
     * @see console\controllers\kfss\PricesController
     *
     * index rebuild
     * @return bool
     * @throws \yii\db\Exception
     */
    public function priceIndex()
    {

        //\Yii::$app->db->createCommand("SET sql_mode = '';")->execute();

        $basePriceRow = \Yii::$app->db->createCommand("SELECT id FROM `shop_type_price` WHERE `code` = 'BASE'")->queryOne();
        $basePriceId = $basePriceRow['id'];

        $sSPriceRow = \Yii::$app->db->createCommand("SELECT id FROM `shop_type_price` WHERE `code` = 'SHOPANDSHOW'")->queryOne();
        $sSPriceId = $sSPriceRow ? $sSPriceRow['id'] : $basePriceId;

        $query = <<<SQL
INSERT INTO ss_shop_product_prices (product_id, type_price_id, price, min_price, max_price, discount_percent)
    SELECT t.*, ROUND(((max_price - price) / max_price) * 100 ) AS discount_percent
    FROM (
        SELECT product_id, type_price_id, IF(price = 0, min_price, price) AS price, min_price, max_price
        FROM (
        SELECT product.id AS product_id, COALESCE(stp_offer.id, {$basePriceId}) AS type_price_id, COALESCE(sp_offer.price, sp_base.price) AS price,
            COALESCE(NULLIF(MIN(COALESCE(sp_min_max_offer.price, sp_min_max_product.price)), NULL), COALESCE(sp_offer.price, sp_base.price)) AS min_price, 
            MAX(COALESCE(sp_max_offer.price, sp_max_product.price)) AS max_price
              
            FROM cms_content_element AS product
                
            LEFT JOIN cms_content_element child_cards ON child_cards.parent_content_element_id = product.id
            LEFT JOIN cms_content_element child_products ON child_products.parent_content_element_id = child_cards.id
            
            LEFT JOIN cms_content_element_property price_active_id ON price_active_id.element_id = COALESCE(product.parent_content_element_id, product.id)
               AND price_active_id.property_id = (SELECT id FROM cms_content_property WHERE code = 'PRICE_ACTIVE' AND content_id = 2)
            
            LEFT JOIN shop_type_price AS stp_offer ON stp_offer.id = price_active_id.value
        
            LEFT JOIN shop_product_price AS sp_offer ON sp_offer.product_id = product.id AND (sp_offer.type_price_id = stp_offer.id)
            LEFT JOIN shop_product_price AS sp_base ON sp_base.type_price_id = {$basePriceId} AND sp_base.product_id = product.id
            
            -- Минимальная цена товаров и предложений
            LEFT JOIN shop_product_price AS sp_min_max_offer ON sp_min_max_offer.product_id IN (child_products.id) AND sp_min_max_offer.price > 0  
                  AND (sp_min_max_offer.type_price_id = stp_offer.id OR sp_min_max_offer.type_price_id = {$basePriceId})
            LEFT JOIN shop_product_price AS sp_min_max_product ON sp_min_max_product.product_id = product.id AND sp_min_max_product.price > 0 
                  AND sp_min_max_product.type_price_id = {$basePriceId}
            
            -- Максимальная цена товаров и предложений от типа цена ШШ
            LEFT JOIN shop_product_price AS sp_max_offer ON sp_max_offer.product_id IN (child_products.id) AND sp_max_offer.price > 0  
                  AND (sp_max_offer.type_price_id = {$sSPriceId})
            LEFT JOIN shop_product_price AS sp_max_product ON sp_max_product.product_id = product.id AND sp_max_product.price > 0 
                  AND sp_max_product.type_price_id = {$sSPriceId}
            
            WHERE product.content_id IN (2, 10) 
              AND (product.id = :product_id or child_products.id = :product_id)
            
            GROUP BY product.id 
        ) AS t
    ) AS t
ON DUPLICATE KEY UPDATE type_price_id=VALUES(type_price_id),  price=VALUES(price),  min_price=VALUES(min_price),  max_price=VALUES(max_price),  discount_percent=VALUES(discount_percent)
SQL;


        try {

            //$transaction = \Yii::$app->db->beginTransaction();

            $affected = \Yii::$app->db->createCommand($query, [
                ':product_id' => $this->cmsContentElement->id
            ])->execute();

            //$transaction->commit();

        } catch (\yii\db\Exception $e) {

            Job::dump($e->getMessage());

            return false;
            //$transaction->rollBack();
//            throw $e;
            //return false;

        }

        return true;

    }
}