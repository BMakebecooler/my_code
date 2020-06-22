<?php

namespace common\models\query;

use common\models\NewProduct;
use common\models\Product;

/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 09/01/2019
 * Time: 18:55
 */
class NewProductQuery extends \yii\db\ActiveQuery
{


    public function onlyActive()
    {
        return $this->andWhere(['cms_content_element.active' => 'Y']);
    }

    /**Условие активности родительского элемента (например для карты, активность у лота)
     *
     * @return $this
     */
    public function onlyActiveParent()
    {
        return $this->innerJoin('cms_content_element AS parent_element', 'parent_element.id = cms_content_element.parent_content_element_id')
            ->andWhere('parent_element.active="Y"');
    }

    public function available()
    {
        return $this->andWhere(['>', 'new_quantity', 0]);
    }

    public function onlyIs999()
    {
        return $this
            ->andWhere(['ss_shop_product_prices.price' => 999]);
    }

    public function hasQuantity()
    {
        return $this->andWhere(['>', 'new_quantity', 0]);
    }


    public function treeNotNull()
    {
        return $this->andWhere('cms_content_element.tree_id is not null');
    }

    public function prizeMoreThanZero()
    {
        return $this->andWhere(['>', 'ss_shop_product_prices.price', 2]);
    }

    public function priceMoreThanZero()
    {
        return $this->innerJoin('ss_shop_product_prices', 'ss_shop_product_prices.product_id=cms_content_element.id')
            ->andWhere(['>', 'ss_shop_product_prices.price', 2]);
    }

    public function priceValueOne()
    {
        return $this->innerJoin('ss_shop_product_prices', 'ss_shop_product_prices.product_id=cms_content_element.id')
            ->andWhere(['ss_shop_product_prices.price' => 1]);
    }

    public function onlyLot()
    {
        return $this->andWhere(['cms_content_element.content_id' => NewProduct::LOT]);
    }

    public function onlyCard()
    {
        return $this->andWhere(['cms_content_element.content_id' => NewProduct::CARD]);
    }

    public function byContent($array)
    {
        return $this->andWhere(['cms_content_element.content_id' => $array]);
    }

    public function onlyModification()
    {
        return $this->andWhere(['cms_content_element.content_id' => NewProduct::MOD]);
    }


    public function onlyPublic()
    {
        return $this->leftJoin('cms_content_element_property', 'cms_content_element_property.element_id = cms_content_element.id and cms_content_element_property.property_id = 83')
            ->andWhere('cms_content_element_property.value IS NULL OR cms_content_element_property.value <> "Y"');
    }

    /**
     * Для карточек товаров свойство Не показывать на сайте необходимо брать из родителя (лота)
     * @return $this
     */
    public function onlyPublicForCards()
    {
        return $this->leftJoin('cms_content_element_property AS not_public_card', 'not_public_card.element_id = cms_content_element.parent_content_element_id and not_public_card.property_id = 83')
            ->andWhere('not_public_card.value IS NULL OR not_public_card.value <> "Y"');
    }

    public function byCode($code)
    {
        return $this->andWhere(['code' => $code]);
    }

    public function byGuid($guid)
    {
        return $this->innerJoin('ss_guids', 'cms_content_element.guid_id=ss_guids.id')
            ->andWhere(['guid' => $guid]);
    }

    public function imageIdNotNull()
    {
        return $this->andWhere('cms_content_element.image_id is not null');
    }

    public function forFeed()
    {

        return $this->onlyActive()
//            ->onlyActiveParent()
            ->onlyPublic()
//            ->onlyLot()
//            ->onlyCard()
            ->byContent([Product::CARD, Product::LOT])
            ->onlyActive()
            ->onlyPublicForCards()
//            ->available()
            ->prizeMoreThanZero()
            ->treeNotNull();
//            ->imageIdNotNull();
    }

    public function priceType($typeId)
    {
        return $this->andWhere(['new_price_active' => $typeId]);
    }
}