<?php

namespace common\models\cmsContent;

use common\models\Product;
use modules\shopandshow\models\shop\ShopProduct;
use modules\shopandshow\models\shop\SsShopProductPrice;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsContentElement as SXCmsContentElement;
use skeeks\cms\models\CmsContentElementProperty;
use skeeks\cms\models\CmsContentPropertyEnum;

class ContentElementBrand extends SXCmsContentElement
{

    /**
     * Признак необходимости отображения картинки бренда
     * @var
     */
    public $show_brand_image;

    /**
     * Кол-во активных товаров связанных с брендом
     * @var
     */
    public $products_num;

    public function init()
    {
        parent::init();
    }

    /**
     * Метод проверки условия необходимо ли отображать изображение для бренда
     *
     * @return bool
     */
    public function isShowBrandImage(){
        return (bool)($this->show_brand_image && $this->show_brand_image == Cms::BOOL_Y);
    }

    /**
     * Добавление в запрос части по выборке свойста Отображать ли изображение бренда на странице брендов
     *
     * @param \yii\data\ActiveDataProvider $activeDataProvider
     * @return \yii\db\QueryInterface
     */
    public static function addImagePropConditions(\yii\data\ActiveDataProvider $activeDataProvider){
        $query = $activeDataProvider->query;

        $query->leftJoin(CmsContentProperty::tableName() . ' AS content_property_show_image',
            "content_property_show_image.code='show_brand_image'"
        )
            ->leftJoin(CmsContentElementProperty::tableName() . ' AS element_property_show_image',
                "element_property_show_image.property_id=content_property_show_image.id
				                AND element_property_show_image.element_id=".CmsContentElement::tableName().".id")

            ->leftJoin(CmsContentPropertyEnum::tableName() . ' AS content_property_enum_show_image',
                "content_property_enum_show_image.id=element_property_show_image.value")

            ->select([
                self::tableName() . '.*',
                'content_property_enum_show_image.code AS show_brand_image'
            ]);

        return $query;
    }

    /**
     * Добавление условий для учета кол-ва активных товаров связанных с брендом
     *
     * @param \yii\data\ActiveDataProvider $activeDataProvider
     * @return \yii\db\QueryInterface
     */
    public static function addWithActiveProductsConditions(\yii\data\ActiveDataProvider $activeDataProvider){
        $query = $activeDataProvider->query;

        $query->leftJoin(CmsContentElementProperty::tableName() . ' AS product_brand_prop',
            "product_brand_prop.property_id = (SELECT id FROM ".CmsContentProperty::tableName()." WHERE code='BRAND') 
            AND product_brand_prop.value = ".self::tableName().".id")

            ->innerJoin(self::tableName() . ' AS product',
            "product.id=product_brand_prop.element_id 
            AND product.active='".Cms::BOOL_Y."'
            AND product.tree_id IS NOT NULL")

            ->innerJoin(SsShopProductPrice::tableName().' AS product_prices',
                "product_prices.product_id=product.id AND product_prices.min_price > 2")
            ->andWhere(Product::tableName(). '.new_quantity >= 1');

//            ->innerJoin(ShopProduct::tableName().' AS shop_product',
//                "shop_product.id=product.id AND shop_product.quantity >= 1");

        return $query;
    }

}