<?php

namespace modules\shopandshow\components\export;


use common\helpers\Category;
use common\helpers\Size;
use common\helpers\Url;
use common\interfaces\Export;
use common\models\NewProduct;
use common\models\Product;
use common\models\ProductParam;
use Exception;
use common\helpers\Feed;
use skeeks\cms\mail\helpers\Html;

class RetailRocketHandler extends YandexHandler implements Export
{
    //Категории для типа одежы низ
    public static $bottomNames = [
        'брюки',
        'леггинсы',
        'джеггинсы',
        'джинсы',
        'шорты',
    ];

    public function init()
    {

        parent::init(); // TODO: Change the autogenerated stub
        $this->name = 'Retail Rocket Export';


        if (!$this->file_path) {
            $this->file_path = "/export/retailrocket/feed.xml";
        }
    }


    public function getNameForCPC()
    {
        return 'retailrocker';
        // TODO: Implement getNameForCPC() method.
    }

    private function _addSizeFloctoryParams(\DOMNode $xoffer, Product $element)
    {
        $sizes = Size::getCardSizes($element->id);

        if ($sizes && count($sizes)) {
            $sizeType = null;
            $lot = $element->lot;
            if ($lot && isset($lot->tree)) {
                //Если товар в категориях мода
                if (Category::checkIsModa($lot->tree)) {
                    //По умолчанию тип одежды верх
                    $sizeType = 'xTop';
                    $lotName = mb_strtolower($lot->name);

                    //Проверяем относится ли одежда к типу одежды низ
                    foreach (self::$bottomNames as $name) {
                        $nameArray = explode($name, $lotName);
                        if (count($nameArray) == 2) {
                            $sizeType = 'xBottom';
                            break;
                        }
                    }
                    //если товар в категориях обувь
                } elseif (Category::checkIsFootwear($lot->tree)) {
                    $sizeType = 'xShoe';
                    //если товар в категориях ювелирка
                } elseif (Category::checkIsJewelry($lot->tree)) {
                    $sizeType = 'xRing';
                }

                if ($sizeType) {
                    $param = $xoffer->appendChild(new \DOMElement('param', implode(',', $sizes)));
                    $param->setAttribute('name', 'avaliable_size');

                    $param = $xoffer->appendChild(new \DOMElement('param', $sizeType));
                    $param->setAttribute('name', 'size_type');
                }
            }

        }

        return $this;
    }

    //todo не использовать этот метод, пока на стороне фронта не выкатят необходимые настройки!
    public function _initOfferArrayNew(\DOMNode $xoffers, Product $element)
    {

        //Что бы не переписывать через чур много пока будем использовать местами старую модельку
        $elementOld = NewProduct::findOne($element->id);

        //Разбиваем на модификации
        $modifications = Product::getProductOffers($element->id);
        if (!$modifications) {
            return null;
        }

        $availability = false;

        if ($element->isCard() && isset($this->analyticsProductsForFeed[$element->parent_content_element_id])) {
            $analyticsProduct = $this->analyticsProductsForFeed[$element->parent_content_element_id];
            if ($analyticsProduct['actual']) {
                $availability = $element->new_quantity > 0;
            }
        }

        $codeSize = null;

        if (Category::checkIsModa($element->lot->tree)) {
            $codeSize = 'KFSS_ETALON___ODEJDA';
        }
        if (Category::checkIsFootwear($element->lot->tree)) {
            $codeSize = 'KFSS_RAZMER_OBUVI';
        }
        if (Category::checkIsJewelry($element->lot->tree)) {
            $codeSize = 'KFSS_RAZMER_KOLTSA';
        }

        $size = null;

        foreach ($modifications as $mod) {
            if ($mod->parent_content_element_id != $element->id) {
                continue;
            }

            if ($codeSize) {
                $size = ProductParam::getParamModificationByCode($mod->id, [$codeSize]);
                if (!$size) {
                    continue;
                }
            }

            $xoffer = $xoffers->appendChild(new \DOMElement('offer'));
            $xoffer->appendChild(new \DOMAttr('id', $mod->id));
//            $xoffer->appendChild(new \DOMAttr('type', 'vendor.model'));

            if ($availability) {
                $xoffer->appendChild(new \DOMAttr('available', $element->new_quantity > 0 ? 'true' : 'false'));
            } else {
                $xoffer->appendChild(new \DOMAttr('available', 'false'));
            }
            if ($size) {
                $xoffer->appendChild(new \DOMAttr('group_id', $element->id));
            } else {
                $xoffer->appendChild(new \DOMAttr('group_id', $mod->id));
            }

            $xoffer->appendChild(new \DOMElement('url', htmlspecialchars(Url::createUrlForFeed($elementOld, $this))));
            if ($element->hasDiscount()) {
                $xoffer->appendChild(new \DOMElement('price', round($element->new_price, 1)));
                $xoffer->appendChild(new \DOMElement('oldprice', round($element->new_price_old, 1)));
            } else {
                $xoffer->appendChild(new \DOMElement('price', round($element->new_price, 1)));
            }
            $xoffer->appendChild(new \DOMElement('categoryId', $elementOld->getCategoryId()));
            $xoffer->appendChild(new \DOMElement('picture', $elementOld->getStorageFile()));
            $xoffer->appendChild(new \DOMElement('name', htmlspecialchars($elementOld->getNameForFeed())));

            if ($size) {
                $param = $xoffer->appendChild(new \DOMElement('param', $size->name));
                $param->setAttribute('name', 'Размер');
//                $param->setAttribute('unit', 'RU');
            }

            $colorData = $element->getColorData();
            if ($colorData) {
                $param = $xoffer->appendChild(new \DOMElement('param', $colorData['name']));
                $param->setAttribute('name', 'Цвет');
            }

            $technicalDetail = $elementOld->relatedPropertiesModel->getAttribute('HARAKTERISTIKI');
            if ($technicalDetail) {
                $technicalDetail = Feed::remove($technicalDetail);
                try {
                    $technicalDetail = "<![CDATA[" . $technicalDetail . "]]>";
                    // todo  disable because not valid yandex
                    $xoffer->appendChild(new \DOMElement('description', $technicalDetail));
                } catch (Exception $e) {
                }
            }

            $brandName = $element->brand ? Html::encode($element->brand->name) : false;
            if ($brandName) {
                $xoffer->appendChild(new \DOMElement('vendor', $brandName));
            }
        }
        return $this;

    }


    public function _initOfferArray(\DOMNode $xoffers, Product $element)
    {
        //Что бы не переписывать через чур много пока будем использовать местами старую модельку
        $elementOld = NewProduct::findOne($element->id);

        $xoffer = $xoffers->appendChild(new \DOMElement('offer'));
        $xoffer->appendChild(new \DOMAttr('id', $element->id));

//        $availability = $elementOld->getIsAvailability();

        //* Stock status *//

        $availability = false;

        if ($element->isCard() && isset($this->analyticsProductsForFeed[$element->parent_content_element_id])) {
            $analyticsProduct = $this->analyticsProductsForFeed[$element->parent_content_element_id];
            if ($analyticsProduct['actual']) {
                $availability = $element->new_quantity > 0;
            }
        }

        //* /Stock status *//


        $xoffer->appendChild(new \DOMAttr('available', $availability ? 'true' : 'false'));

        if ($element->parent_content_element_id) {
            $xoffer->appendChild(new \DOMAttr('group_id', $element->parent_content_element_id));
        }

        $xoffer->appendChild(new \DOMElement('url', htmlspecialchars(Url::createUrlForFeed($elementOld, $this))));

        $xoffer->appendChild(new \DOMElement('name', htmlspecialchars($elementOld->getNameForFeed())));

        $xoffer->appendChild(new \DOMElement('picture', $elementOld->getStorageFile()));

        $xoffer->appendChild(new \DOMElement('categoryId', $elementOld->getCategoryId()));

//        var_dump($element->getIsBasePrice());
//        die();

        if ($element->hasDiscount()) {
            $xoffer->appendChild(new \DOMElement('price', round($element->new_price, 1)));
            $xoffer->appendChild(new \DOMElement('oldprice', round($element->new_price_old, 1)));
        } else {
            $xoffer->appendChild(new \DOMElement('price', round($element->new_price, 1)));
        }

//        if ($element->getIsBasePrice()) {
//            $xoffer->appendChild(new \DOMElement('price', round($element->getCurrentPrice(), 1)));
//        } else {
//            $xoffer->appendChild(new \DOMElement('price', round($element->getCurrentPrice(), 1)));
//            $xoffer->appendChild(new \DOMElement('oldprice', round($element->getOldPrice(), 1)));
//        }

        $xoffer->appendChild(new \DOMElement('currencyId', 'RUB'));


        $technicalDetail = $elementOld->relatedPropertiesModel->getAttribute('HARAKTERISTIKI');
        if ($technicalDetail) {
            $technicalDetail = Feed::remove($technicalDetail);
            try {
                $technicalDetail = "<![CDATA[" . $technicalDetail . "]]>";
                // todo  disable because not valid yandex
                $xoffer->appendChild(new \DOMElement('description', $technicalDetail));
            } catch (Exception $e) {
            }
        }


        if ($this->default_delivery) {
            if ($this->default_delivery == 'Y') {
                $xoffer->appendChild(new \DOMElement('delivery', 'true'));
            } else if ($this->default_delivery == 'N') {
                $xoffer->appendChild(new \DOMElement('delivery', 'false'));
            }
        }

        if ($this->default_store) {
            if ($this->default_store == 'Y') {
                $xoffer->appendChild(new \DOMElement('store', 'true'));
            } else if ($this->default_store == 'N') {
                $xoffer->appendChild(new \DOMElement('store', 'false'));
            }
        }

        if ($this->default_pickup) {
            if ($this->default_pickup == 'Y') {
                $xoffer->appendChild(new \DOMElement('pickup', 'true'));
            } else if ($this->default_pickup == 'N') {
                $xoffer->appendChild(new \DOMElement('pickup', 'false'));
            }
        }

        if ($this->default_sales_notes) {
            $xoffer->appendChild(new \DOMElement('sales_notes', $this->default_sales_notes));
        }

        //добавляем новый параметр sklad
        if ($availability) {
            $stock = new \DOMElement('param', 1);
            $xoffer->appendChild($stock);
            $stock->setAttribute('name', 'sklad');
        }
        $this->_addSizeFloctoryParams($xoffer, $element);

        return $this;
    }

}