<?php

namespace modules\shopandshow\components\export;


use common\helpers\Url;
use common\interfaces\ExportType;
use common\lists\TreeList;
use common\models\CmsTreeProperty;
use common\models\CmsTreeTypeProperty;
use common\models\generated\models\CmsStorageFile;
use common\models\NewProduct;
use common\models\Product;
use common\models\Tree;
use DOMDocument;
use skeeks\cms\helpers\FileHelper;
use common\helpers\Common;

class GoogleHandler extends BaseHandler implements ExportType
{
    public $xml = null;

    private $trees = [];

    protected static $googleCategoryNames = [];

    public function init()
    {
        static::initTimer("init");

        $catalogTree = TreeList::getTreeById(TreeList::CATALOG_ID);
        $this->trees = $catalogTree->getDescendants()->indexBy('id')->all();

        parent::init();
        $this->name = 'Google Merchant';

        if (!$this->file_path) {
            $this->file_path = "/export/google/feed.xml";
        }

        //получаем массив всех Google Categories
        $queryCategoryNames = CmsTreeProperty::find()
            ->select(['element_id', 'value'])
            ->andWhere([
                'property_id' => CmsTreeTypeProperty::find()
                    ->select('id')
                    ->andWhere(['code' => CmsTreeTypeProperty::GOOGLE_CATEGORY_NAME_CODE])
            ])
            ->asArray();

        foreach ($queryCategoryNames->each() as $row) {
            if ($row['value']) {
                self::$googleCategoryNames[$row['element_id']] = $row['value'];
            }
        }

        static::showTimer("init");

    }

    public function getNameType()
    {
        return 'google';
    }

    public function export()
    {
        static::initTimer("export");

        //TODO: почему то свойство $this->file_path обнуляется
        if (Common::getObjectClassShortName($this) == 'ExportShopGoogleMerchantFlashPriceHandler') {
            $this->file_path = "/export/google/feed-flashprice.xml";
        }

        //TODO: для тестирования
//        $this->file_path = '/export/google/feedTestFast.xml';

        //TODO: if console app
        \Yii::$app->urlManager->baseUrl = $this->base_url;
        \Yii::$app->urlManager->scriptUrl = $this->base_url;

        ini_set("memory_limit", "8192M");
        set_time_limit(0);

        //Создание дирректории
        static::initTimer("createDir");

        if ($dirName = dirname($this->rootFilePath)) {
            $this->result->stdout("Создание директории\n");

            if (!is_dir($dirName) && !FileHelper::createDirectory($dirName)) {
                throw new \Exception("Не удалось создать директорию для файла");
            }
        }
        static::showTimer("createDir");

        $this->xml = new DOMDocument('1.0', 'utf-8');

        $rss = $this->xml->createElement('rss');
        $rss->setAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
        $rss->setAttribute('version', '2.0');

        $this->xml->appendChild($rss);

        $this->result->stdout("\tДобавление основной информации\n");

        $channel = $this->xml->createElement('channel');
        $rss->appendChild($channel);

        $channel->appendChild($this->xml->createElement('title', $this->shop_name ? htmlspecialchars($this->shop_name) : htmlspecialchars(\Yii::$app->name)));
        $channel->appendChild($this->xml->createElement('link', htmlspecialchars(
            $this->base_url
        )));
        $channel->appendChild($this->xml->createElement('description', $this->shop_company ? htmlspecialchars($this->shop_company) : htmlspecialchars(\Yii::$app->name)));

        static::showTimer("export");

        $this->_appendOffersArray($channel);
        $this->result->stdout("\tДобавление основной информации2\n");

        static::initTimer("save_file");
        $this->xml->formatOutput = true;
        $this->xml->save($this->rootFilePath);
        static::showTimer("save_file");

        return $this->result;
    }

    /**
     * работает в десятки раз быстрее, чем старый метод
     * @param \DOMElement $channel
     *
     * @return bool
     */
    public function _appendOffersArray(\DOMElement $channel)
    {
        static::initTimer("calculate_all_records");

        $analyticsProductsForFeed = $this->getAnalyticsProductsForFeedArray();

        $countAll = $this->getProductQuery()
            ->addGroupBy(Product::tableName() . '.id')
            ->andWhere(['lot.code' => array_keys($analyticsProductsForFeed)])
            ->count();

        static::showTimer("calculate_all_records");

        $count = 0;

        for ($i = 0; $i < ceil($countAll / $this->partSize); $i++) {

            static::initTimer("write_part_items_to_file");

            $command = $this->getProductQuery()
                ->select([
                    Product::tableName() . '.*',
                    'lot.new_brand_id AS lot_new_brand_id',
                    'lot.tree_id AS lot_tree_id',
                    'lot.new_lot_num AS lot_new_lot_num',
                    'lot.image_id AS lot_image_id',
                    'card_storage_file.cluster_file AS image',
                    'lot_storage_file.cluster_file AS parent_image',
                ])
                ->leftJoin(CmsStorageFile::tableName() . ' AS card_storage_file',
                    'card_storage_file.id=' . Product::tableName() . '.image_id')
                ->leftJoin(CmsStorageFile::tableName() . ' AS lot_storage_file',
                    'lot_storage_file.id=lot.image_id')
                ->andWhere(['lot.code' => array_keys($analyticsProductsForFeed)])
                ->limit($this->partSize)
                ->offset($i * $this->partSize)
                ->createCommand();

            $rows = $command->queryAll();

            foreach ($rows as $element) {

                if ($this->isArrForFeedByAnalytics($element)) {

                    static::initTimer("generate_google_feed_element");

                    $stockStatus = 'out of stock';

                    $analyticsProduct = $this->analyticsProductsForFeed[$element['parent_content_element_id']] ?? null;
                    if ($analyticsProduct['actual']) {

                        //* ДопПроверка для фида с выгодой на час *//

                        //Если товар из выгоды - проверяем реальный остаток, иначе - типа нет такого товара
                        if (
                            Common::getObjectClassShortName($this) == 'ExportShopGoogleMerchantFlashPriceHandler'
                            && !isset($this->flashPriceProducts[$element['parent_content_element_id']])
                        ) {
                            $stockStatus = 'out of stock';
                        } else {
                            $stockStatus = $element->new_quantity > 0 ? 'in stock' : 'out of stock';
                        }
                    }

                    $urlProduct = Url::getUrlCardForFeed($element, $element['lot_new_lot_num']);
                    $urlImage = Url::getUrlImageForFeed($element);

                    $xoffer = $this->xml->createElement('item');
                    $channel->appendChild($xoffer);
                    $reqPrefix = 'g:';

                    $title = $element['lot_name'] ?? $element['name'];

                    $xoffer->appendChild($this->xml->createElement($reqPrefix . 'id', $element['id']));
                    $xoffer->appendChild($this->xml->createElement($reqPrefix . 'product_type', htmlspecialchars($this->getProductTypeFromArray($element))));
                    $xoffer->appendChild($this->xml->createElement($reqPrefix . 'title', htmlspecialchars(strip_tags($title))));
                    $xoffer->appendChild($this->xml->createElement($reqPrefix . 'description', htmlspecialchars(strip_tags($element['name']))));
                    $xoffer->appendChild($this->xml->createElement($reqPrefix . 'link', htmlspecialchars($urlProduct)));
                    $xoffer->appendChild($this->xml->createElement($reqPrefix . 'image_link', htmlspecialchars($urlImage)));

                    $xoffer->appendChild($this->xml->createElement($reqPrefix . 'availability', $stockStatus));

                    if ($element['new_price'] < $element['new_price_old']) {
                        $xoffer->appendChild($this->xml->createElement($reqPrefix . 'price', round($element['new_price_old'], 1) . ' RUB'));
                        $xoffer->appendChild($this->xml->createElement($reqPrefix . 'sale_price', round($element['new_price'], 1) . ' RUB'));
                    } else {
                        $xoffer->appendChild($this->xml->createElement($reqPrefix . 'price', round($element['new_price'], 1) . ' RUB'));
                    }

                    if ($element['lot_new_brand_id'] && isset(static::$brands[$element['lot_new_brand_id']])) {
                        $xoffer->appendChild($this->xml->createElement('brand', htmlspecialchars(strip_tags(static::$brands[$element['lot_new_brand_id']]))));
                    }

                    if ($element['lot_tree_id'] && isset(static::$googleCategoryNames[$element['lot_tree_id']])) {
                        $xoffer->appendChild($this->xml->createElement('google_product_category', htmlspecialchars(static::$googleCategoryNames[$element['lot_tree_id']])));
                    }

                    $xoffer->appendChild($this->xml->createElement('identifier_exists', 'no'));
                    $xoffer->appendChild($this->xml->createElement($reqPrefix . 'condition', 'new'));
                    $xoffer->appendChild($this->xml->createElement('adult', 'no'));

                    $count++;

//                    static::showTimer("generate_google_feed_element");

                }

            }

            static::showTimer("write_part_items_to_file");

        }

        echo $count . ' товаров добавлено в фид' . PHP_EOL;

        return true;

    }

    /**
     * @param \DOMElement $channel
     *
     * @return bool
     */
    public function _appendOffersArrayOriginal(\DOMElement $channel)
    {
        static::initTimer("_appendOffersArray");

        $count = 0;
        /** @var Product $element */
        foreach ($this->getProductQuery()->each() as $element) {


            //Не добавляем товары которых нет в аналитике (перебираем карты, а в аналитике лоты, не путать)
//            if ($element->isCard() && !isset($this->analyticsProductsForFeed[$element->parent_content_element_id])){
//                echo "[{$i}] SKIP product [{$element->id}] {$element->name}}" . PHP_EOL;
//                continue;
//            }
            if ($this->isForFeedByAnalytics($element)) {
                $this->_initOfferArray($channel, $element);
                $count++;
            }
        }
//        if (self::$profileMode) {
//            echo Common::getTimerTime("_appendOffersArray") . PHP_EOL;
//        }
        echo $count . ' товаров добавлено в фид' . PHP_EOL;

        static::showTimer("_appendOffersArray");

        return true;
    }

    /**
     * @param \DOMElement $channel
     * @param $element
     * @return $this
     */
    public function _initOfferArray(\DOMElement $channel, Product $element)
    {
        static::initTimer("generate_google_feed_element");

        $reqPrefix = 'g:';

        //Что бы не переписывать через чур много пока будем использовать местами старую модельку
        $elementOld = NewProduct::findOne($element->id);

        //* Stock status *//

        $stockStatus = 'out of stock';

        if ($element->isCard() && isset($this->analyticsProductsForFeed[$element->parent_content_element_id])) {
            $analyticsProduct = $this->analyticsProductsForFeed[$element->parent_content_element_id];
            if ($analyticsProduct['actual']) {

                //* ДопПроверка для фида с выгодой на час *//

                //Если товар из выгоды - проверяем реальный остаток, иначе - типа нет такого товара
                if (
                    Common::getObjectClassShortName($this) == 'ExportShopGoogleMerchantFlashPriceHandler'
                    && !isset($this->flashPriceProducts[$element->parent_content_element_id])
                ) {
                    $stockStatus = 'out of stock';
                } else {
                    $stockStatus = $element->new_quantity > 0 ? 'in stock' : 'out of stock';
                }
            }
        }

        //* /Stock status *//

        $xoffer = $this->xml->createElement('item');
        $channel->appendChild($xoffer);

        $xoffer->appendChild($this->xml->createElement($reqPrefix . 'id', $element->id));
        $xoffer->appendChild($this->xml->createElement($reqPrefix . 'product_type', htmlspecialchars($this->getProductType($element))));
        //$xoffer->appendChild($this->xml->createElement($reqPrefix . 'title', htmlspecialchars(strip_tags($element->getNameForFeed()))));
        $xoffer->appendChild($this->xml->createElement($reqPrefix . 'title', htmlspecialchars(strip_tags($element->getLotName()))));
        $xoffer->appendChild($this->xml->createElement($reqPrefix . 'description', htmlspecialchars(strip_tags($element->name))));
//        $xoffer->appendChild($this->xml->createElement($reqPrefix . 'description', htmlspecialchars(strip_tags($element['product_description']))));
        $xoffer->appendChild($this->xml->createElement($reqPrefix . 'link', htmlspecialchars(Url::createUrlForFeed($elementOld, $this))));
        $xoffer->appendChild($this->xml->createElement($reqPrefix . 'image_link', htmlspecialchars($elementOld->getStorageFile())));
//        $xoffer->appendChild($this->xml->createElement($reqPrefix . 'availability', $element->childrenContentElements->shopProduct->quantity > 0 ? 'in stock' : 'out_of_stock'));
        $xoffer->appendChild($this->xml->createElement($reqPrefix . 'availability', $stockStatus));

        if ($element->hasDiscount()) {
            $xoffer->appendChild($this->xml->createElement($reqPrefix . 'price', round($element->new_price_old, 1) . ' RUB'));
            $xoffer->appendChild($this->xml->createElement($reqPrefix . 'sale_price', round($element->new_price, 1) . ' RUB'));
        } else {
            $xoffer->appendChild($this->xml->createElement($reqPrefix . 'price', round($element->new_price, 1) . ' RUB'));
        }


//        if ($element->getIsBasePrice()) {
//            $xoffer->appendChild($this->xml->createElement($reqPrefix . 'price', round($element->getCurrentPrice(), 1) . ' RUB'));
//        } else {
//            $xoffer->appendChild($this->xml->createElement($reqPrefix . 'price', round($element->getOldPrice(), 1) . ' RUB'));
//            $xoffer->appendChild($this->xml->createElement($reqPrefix . 'sale_price', round($element->getCurrentPrice(), 1) . ' RUB'));
//        }

        $xoffer->appendChild($this->xml->createElement('google_product_category', htmlspecialchars($element->getGoogleCategoryName())));
        $xoffer->appendChild($this->xml->createElement('brand', htmlspecialchars(strip_tags($element->getBrandName()))));
//        //Если у товара нет ни кода GTIN, ни кода MPN
        $xoffer->appendChild($this->xml->createElement('identifier_exists', 'no'));
        $xoffer->appendChild($this->xml->createElement($reqPrefix . 'condition', 'new'));
        $xoffer->appendChild($this->xml->createElement('adult', 'no'));

        static::showTimer("generate_google_feed_element");

        return $this;
    }

    /**
     * атрибут product_type - дерево категорий на сайте
     * @param array $element
     * @return string
     */
    public function getProductTypeFromArray(array $element)
    {
        /** @var Tree $tree */
        $tree = $this->trees[$element['lot_tree_id']] ?? null;
        if (!$tree) return '';

        $result = [];

        while ($tree) {
            $result[] = $tree->name;
            if (!$tree->pid || $tree->pid == TreeList::CATALOG_ID) break;

            $tree = $this->trees[$tree->pid] ?? null;
        }

        return join(' > ', array_reverse($result));
    }

    /**
     * атрибут product_type - дерево категорий на сайте
     * @param array $element
     * @return string
     */
    public function getProductType(Product $element)
    {
        /** @var Tree $tree */
        $tree = $this->trees[$element->tree_id] ?? null;
        if (!$tree) return '';

        $result = [];

        while ($tree) {
            $result[] = $tree->name;
            if (!$tree->pid || $tree->pid == TreeList::CATALOG_ID) break;

            $tree = $this->trees[$tree->pid];
        }

        return join(' > ', array_reverse($result));
    }
}