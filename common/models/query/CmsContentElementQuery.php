<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-03-20
 * Time: 19:57
 */

namespace common\models\query;


use common\helpers\Common;
use common\models\cmsContent\CmsContentElement;
use common\models\CmsContentElementProperty;
use common\models\CmsTree;
use common\models\Product;
use common\models\Setting;
use common\models\SsShopProductPrices;

class CmsContentElementQuery extends \common\models\generated\query\CmsContentElementQuery
{

    public $contentId;
    public $tableName;

    public function prepare($builder)
    {
        if ($this->contentId !== null) {
            $this->andWhere(["$this->tableName.content_id" => $this->contentId]);
        }
        return parent::prepare($builder);
    }

    public function init()
    {
        parent::init();

        if (Setting::getIs999()) {
            //$this->price999AndLess();
        }
    }

//    public function byGuid($guid)
//    {
//        return $this
//            ->innerJoin(SsGuids::tableName(), CmsContentElement::tableName().'.id=guid_id')
//            ->andWhere(['OR', ['new_guid' => $guid], ['guid' => $guid]]);
//    }

    public function onlyActive()
    {
        return $this->andWhere([CmsContentElement::tableName() . '.active' => Common::BOOL_Y]);
    }

    /**Условие активности родительского элемента (например для карты, активность у лота)
     *
     * @return $this
     */
    public function onlyActiveParent()
    {
        return $this->innerJoin(CmsContentElement::tableName() . ' AS parent_element',
            'parent_element.id = ' . CmsContentElement::tableName() . '.parent_content_element_id')
            ->andWhere(['parent_element.active' => Common::BOOL_Y]);
    }

    public function onlyIsPrice999()
    {
        return $this
            ->andWhere([SsShopProductPrices::tableName() . '.price' => 999]);
    }

    public function priceType($typeId)
    {
        return $this->andWhere([CmsContentElement::tableName() . '.new_price_active' => $typeId]);
    }

    public function hasQuantity()
    {
        return $this->andWhere(['>', 'new_quantity', 0]);
    }

    public function hasQuantityNew()
    {
        return $this->andWhere(['>', CmsContentElement::tableName() . '.new_quantity', 0]);
    }

    public function treeNotNull()
    {
        return $this->andWhere(CmsContentElement::tableName() . '.tree_id is not null');
    }

    public function treeIsActive($forFeed = true)
    {
        if ($forFeed) {
            return $this->andWhere([CmsTree::tableName() . '.active' => Common::BOOL_Y]);
        } else {
            return $this->leftJoin(CmsContentElement::tableName() . ' as lot',
                'lot.id = ' . CmsContentElement::tableName() . '.parent_content_element_id')
                ->leftJoin(CmsTree::tableName(), CmsTree::tableName() . '.id=lot.tree_id')
                ->andWhere([CmsTree::tableName() . '.active' => Common::BOOL_Y]);
        }
    }

    public function priceMoreThanZero()
    {
        return $this->innerJoin(SsShopProductPrices::tableName(),
            SsShopProductPrices::tableName() . '.product_id=' . CmsContentElement::tableName() . '.id')
            ->andWhere(['>', SsShopProductPrices::tableName() . '.price', 2]);
    }

    public function priceMoreThanZeroNew()
    {
        return $this->andWhere(['>', CmsContentElement::tableName() . '.new_price', 2]);
    }

    public function onlyIs999()
    {
        return $this
            ->andWhere([CmsContentElement::tableName() . '.new_price' => 999]);
    }

    public function price999AndLess()
    {
        return $this
            ->andWhere(['<=', CmsContentElement::tableName() . '.new_price', 999]);
    }

    public function priceValueOne()
    {
        return $this->innerJoin(SsShopProductPrices::tableName(),
            SsShopProductPrices::tableName() . '.product_id=' . CmsContentElement::tableName() . '.id')
            ->andWhere([SsShopProductPrices::tableName() . '.price' => 1]);
    }

    public function onlyLot()
    {
        return $this->andWhere([CmsContentElement::tableName() . '.content_id' => Product::LOT]);
    }

    public function onlyCard()
    {
        return $this->andWhere([CmsContentElement::tableName() . '.content_id' => Product::CARD]);
    }

    public function byContent($array)
    {
        return $this->andWhere([CmsContentElement::tableName() . '.content_id' => $array]);
    }

    public function onlyModification()
    {
        return $this->andWhere([CmsContentElement::tableName() . '.content_id' => Product::MOD]);
    }


    public function onlyPublic()
    {
        return $this->leftJoin(CmsContentElementProperty::tableName(),
            CmsContentElementProperty::tableName() . '.element_id = ' . CmsContentElement::tableName() . '.id 
            and ' . CmsContentElementProperty::tableName() . '.property_id = 83')
            ->andWhere(CmsContentElementProperty::tableName() . '.value IS NULL OR ' . CmsContentElementProperty::tableName() . '.value <> "Y"');
    }

    public function onlyPublicNew()
    {
        return $this->andWhere(CmsContentElement::tableName() . '.new_not_public IS NULL OR ' . CmsContentElement::tableName() . '.new_not_public <> 1');
    }

    /**
     * Для карточек товаров свойство Не показывать на сайте необходимо брать из родителя (лота)
     * @return $this
     */
    public function onlyPublicForCardsNew()
    {
        return $this->leftJoin(CmsContentElement::tableName() . ' as lot',
            'lot.id = ' . CmsContentElement::tableName() . '.parent_content_element_id')
            ->andWhere('lot.new_not_public IS NULL OR lot.new_not_public <> 1');
    }

    /**
     * Для карточек товаров свойство Не показывать на сайте необходимо брать из родителя (лота)
     * @return $this
     */
    public function onlyPublicForCards()
    {
        return $this->leftJoin(CmsContentElementProperty::tableName(),
            CmsContentElementProperty::tableName() . '.element_id = ' . CmsContentElement::tableName() . '.parent_content_element_id 
            and ' . CmsContentElementProperty::tableName() . '.property_id = 83')
            ->andWhere(CmsContentElementProperty::tableName() . '.value IS NULL OR ' . CmsContentElementProperty::tableName() . '.value <> "Y"');
    }

    public function byCode($code)
    {
        return $this->andWhere(['code' => $code]);
    }

    public function byParent($id)
    {
        return $this->andWhere([CmsContentElement::tableName() . '.parent_content_element_id' => $id]);
    }

//    public function byGuid($guid)
//    {
//        return $this->innerJoin('ss_guids', 'cms_content_element.guid_id=ss_guids.id')
//            ->andWhere(['guid' => $guid]);
//    }

    public function imageIdNotNull()
    {
        return $this->andWhere(['not', [CmsContentElement::tableName() . '.image_id' => null]]);
    }

    public function imageIdIsNull()
    {
        return $this->andWhere([CmsContentElement::tableName() . '.image_id' => null]);
    }

    /**
     * @return CmsContentElementQuery
     * @deprecated
     */
    public function notHiddenFromCatalogImage()
    {
        return $this->andWhere(['>', CmsContentElement::tableName() . '.image_id', 0]);
//        return $this->andWhere(['!=', 'cms_content_element.hide_from_catalog_image', 1]);
    }

    public function brandNotEmptyForCard()
    {
        return $this->leftJoin(CmsContentElement::tableName() . ' as lot_brand',
            'lot_brand.id = ' . CmsContentElement::tableName() . '.parent_content_element_id')
            ->andWhere(['not', ['lot_brand.new_brand_id' => null]])
            ->andWhere(['!=', 'lot_brand.new_brand_id', 0]);
    }

    public function forFeed()
    {

        return $this->onlyActive()
//            ->onlyActiveParent()
//            ->onlyPublic()
//            ->onlyLot()
            ->onlyCard()
//            ->byContent([Product::CARD, Product::LOT])
            ->onlyActive()
            ->onlyPublicForCardsNew()
//            ->available()
            ->priceMoreThanZeroNew()
            ->treeNotNull();
//            ->imageIdNotNull();
    }

    public function byBrand($idBrand, $forFeed = true)
    {
        if ($forFeed) {
            return $this->andWhere(['lot.new_brand_id' => $idBrand]);
        } else {
            return $this->leftJoin(CmsContentElement::tableName() . ' as lot_brand',
                'lot_brand.id = ' . CmsContentElement::tableName() . '.parent_content_element_id')
                ->andWhere(['lot_brand.new_brand_id' => $idBrand]);
        }
    }

    public function canSale()
    {
        return $this->onlyActive()
//            ->onlyLot() //Закоментировано так как метод общий, не только для лотов
//            ->imageIdNotNull()
            ->hasQuantityNew()
            ->priceMoreThanZeroNew()
            ->onlyPublicNew()
//            ->notHiddenFromCatalogImage()
            ->treeNotNull();
    }
}