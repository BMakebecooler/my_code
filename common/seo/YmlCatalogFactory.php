<?php

/**
 * @author Arkhipov Andrei <arhan89@gmail.com>
 * @copyright (c) K-Gorod
 * Date: 06.06.2019
 * Time: 16:03
 */

namespace common\seo;


use common\models\NewProduct;
use common\models\TreeFactory;
use modules\shopandshow\models\shop\ShopProduct;
use SimpleXMLElement;
use yii\helpers\Url;

/**
 * Class YmlCatalogFactory
 * @package common\seo
 */
class YmlCatalogFactory
{
    const CURRENCY_CODE_RUB = 'RUR';
    /** @var SimpleXMLElement */
    public $xml;
    /** @var SimpleXMLElement */
    public $nodeShop;

    /**
     * @return YmlCatalogFactory
     */
    public static function create()
    {
        return new static();
    }

    public function make()
    {
        $this->xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><yml_catalog/>');
        $this->xml->addAttribute('date', date('Y-m-d H:i'));

        $this
            ->renderShop()
            ->renderCategories()
            ->renderOffers();

        return $this;
    }

    public function renderShop()
    {
        $this->nodeShop = $this
            ->xml
            ->addChild('shop');

        //shop properties
        $this->nodeShop->addChild('name', htmlspecialchars('Шоп Энд Шоу (Shop & Show)'));
        $this->nodeShop->addChild('company', 'ООО «МаркетТВ»');
        $this->nodeShop->addChild('url', 'https://shopandshow.ru');

        return $this;
    }

    public function renderCurrencies()
    {
        $currencyList = [
            [
                'id' => static::CURRENCY_CODE_RUB,
                'rate' => 'rate'
            ]
        ];
        $currencies = $this
            ->nodeShop
            ->addChild('currencies');

        foreach ($currencyList as $currency) {
            $child = $currencies->addChild('currency');
            $child->addAttribute('id', $currency['id']);
            $child->addAttribute('rate', $currency['rate']);
        }
        return $this;
    }

    public function renderCategories()
    {
        $categories = $this
                ->nodeShop
                ->addChild('categories');

        $categoryList = TreeFactory::create()
            ->findAll();


        if (!empty($categoryList)) {

            foreach ($categoryList->items as $category) {
                $categoryChild = $categories->addChild('category', $category['name']);
                $categoryChild->addAttribute('id', $category['id']);

                if ($category['parent_id']) {
                    $categoryChild->addAttribute('parentId', $category['parent_id']);
                }
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function renderOffers()
    {
        $offers = $this
            ->nodeShop
            ->addChild('offers');

        /** @var NewProduct $items */
        $items = NewProduct::find()
            ->onlyPublic()
            ->orderBy(['id' => SORT_ASC])
            ->limit(10);

        /** @var NewProduct $item */
        foreach ($items->each() as $item) {

            $offer = $offers->addChild('offer');
            $shopProduct = ShopProduct::getInstanceByContentElement($item);

            /*<offer id="159" available="true" bid="35">
                <url>http://www.abc.ru/159.html</url>
                <price>3045.5</price>
                <currencyId>RUR</currencyId>
                <categoryId>1293</categoryId>
                <picture>http://www.abc.ru/1590.jpg</picture>
                <picture>http://www.abc.ru/1591.jpg</picture>
                <store>false</store>
                <delivery>true</delivery>
                <name>Наушники Koss Sporta Pro</name>
                <vendor>Koss</vendor>
                <model>Sporta Pro</model>
                <description>Описание товара</description>
                <sales_notes>Покупка в день заказа</sales_notes>

                <age>0</age>
                <manufacturer_warranty>true</manufacturer_warranty>
                <param name="Тип">12344</param>
                <param name="Материал">asdfgadfg</param>
                <param name="Wi-Fi" unit="">ага</param>
                <param name="Размер экрана" unit="дюйм">27</param>
                <param name="Размер оперативной памяти" unit="Мб">4096</param>
                <param name="Объём жесткого диска" unit="Тб">1</param>
                <param name="Вес" unit="кг">13.8</param>
            </offer>*/
            $offer->addAttribute('id', $item->id);
            $offer->addAttribute('available', $item->isAvailability);
            //item attributes;
            $offer->addChild('url', Url::to([$item->url], true));
            $offer->addChild('price', $shopProduct->getBasePriceMoney());
            $offer->addChild('currencyId', static::CURRENCY_CODE_RUB);
            $offer->addChild('categoryId', $item->getCategoryId());
            $offer->addChild('picture', $item->image ? $item->image->src : null);
            $offer->addChild('store', false);
            $offer->addChild('delivery', true);
            $offer->addChild('name', $item->getLotName());
            $offer->addChild('vendor', $item->getBrandName()); //Название производителя.
            $offer->addChild('description', $item->description_short);
            $offer->addChild('age', 0);
            //$offer->addChild('manufacturer_warranty', true);
            $characteristics = $item->getCharacteristics();

            if(!empty($characteristics)) {

                $DOM = new \DOMDocument();
                $DOM->loadHTML(mb_convert_encoding($characteristics, 'HTML-ENTITIES', 'UTF-8'));
                $items = $DOM->getElementsByTagName('tr');

                foreach ($items as $node) {
                    if($node->childNodes) {
                        $property = $node->childNodes->item(0)->nodeValue;
                        $value = $node->childNodes->item(1) ? $node->childNodes->item(1)->nodeValue : null;
                        $param = $offer->addChild('param', trim($value));
                        $param->addAttribute('name', trim($property));
                    }
                }
            }
        }

        return $this;
    }

    public function render()
    {
        header("Pragma: no-cache");
        header("Content-type: text/xml; charset=utf-8");
        echo $this->xml->asXML();
        die();
    }

    public function save($filename)
    {
        $this->xml->asXML($filename);
    }
}