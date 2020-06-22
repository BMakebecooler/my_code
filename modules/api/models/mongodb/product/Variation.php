<?php

namespace modules\api\models\mongodb\product;

use common\models\cmsContent\CmsContentElement;
use common\models\cmsContent\CmsContentProperty;
use common\widgets\products\ModificationsWidget;
use modules\shopandshow\models\shop\ShopProduct;
use skeeks\cms\components\Cms;

class Variation extends CommonProduct
{

    public function init()
    {
        parent::init();
    }

    /**
     * @return string the name of the index associated with this ActiveRecord class.
     */
    public static function collectionName()
    {
        return 'products_variations';
    }

    /**
     * @return array list of attribute names.
     */
    public function attributes()
    {
        return ['_id', 'name', 'email', 'address', 'status'];
    }

    /**
     * @param CmsContentElement|ShopContentElement $product
     * @return array|boolean
     */
    public static function getData($product)
    {
        if (!$product->price) {
            return false;
        }

//        $shopProduct = ShopProduct::getInstanceByContentElement($product);

        //$attributes = Html::decode($product->relatedPropertiesModel->getAttribute('TECHNICAL_DETAILS'));
        $description = ''; //Html::decode($product->relatedPropertiesModel->getAttribute('PREIMUSHESTVA'));

        //$komplektacia = Strings::bxHtml2br($model->relatedPropertiesModel->getAttribute('KOMPLEKTACIA'));

//        $images[] = [
//            'src' => 'https://img1.shopandshow.ru/uploads/images/element/cb/5c/c8/cb5cc899e6ebbc1aa4905a238f620a94/sx-filter__common-thumbnails-Thumbnail/e2c72e18b9d72a8994b976ff7c692805/3159827-003-159-827.jpg?h=1201&w=1201'
//        ];

        /*        foreach ($product->images as $image) {
                    $image = \Yii::$app->imaging->thumbnailUrlSS($image->src,
                        new Thumbnail([
                            'w' => 408, // 220, // 218
                            'h' => 408, // 220, // 413
                        ])
                    );

                    $images[] = [
                        'src' => $image
                    ];
                }*/

        $modifications = new ModificationsWidget([
            'namespace' => ModificationsWidget::getNameSpace(),
            'model' => $product,
        ]);

        $attributes = [];

        /**
         * @var $property CmsContentProperty
         */
        foreach ($modifications->getParameters() as $property) {

            $optionsData = $modifications->getParameterData($property->code);

            if (!$optionsData) {
                continue;
            }

            $attribute = [
                'id' => $property->id,
                'name' => $property->getItemName(),
            ];

            if (strtolower($property->code) === 'color_ref') {
                $attribute['name'] = 'Цвет';
            }

            foreach ($optionsData as $item) {
                $title = ((bool)$item['quantity']) ? $item['name'] : 'Нет в наличии';
                $attribute['option'] = $title;
            }

            /*            if (strtolower($property->code) === 'color_ref') {
                            $colors = $modifications->getParameterData($property->code);
                            foreach ($colors as $color) {
                                $title = ((bool)$color['quantity']) ? $color['name'] : 'Нет в наличии'; //Добавить image когда адаптируем приложение
                                $attribute['options'][] = $title;
                            }
                        } else {
                            $default = $modifications->getParameterData($property->code);
                            foreach ($default as $item) {
                                $title = ((bool)$item['quantity']) ? $item['name'] : 'Нет в наличии';
                                $attribute['options'][] = $title;
                            }
                        }*/


            $attributes[] = $attribute;
        }

        $isSale = $product->price->price < $product->price->max_price && (self::$basePriceId != $product->price->type_price_id);

        $notPublic = $product->relatedPropertiesModel->getAttribute('NOT_PUBLIC');
        $isActive = $notPublic == Cms::BOOL_Y ? false : true; //TODO:: Добавить quantity

        return [
            'id' => $product->id,
            'product_id' => $product->parent_content_element_id,
            'bid' => $product->bitrix_id,
            'name' => $product->name,
            'active' => $isActive,

//            'category_id' => $product->tree_id,
//            'category_name' => $product->cmsTree->name,
//            'slug' => $product->code,

            'permalink' => $product->absoluteUrl,
            'description' => $description,

            'price' => $product->price->price,
            'regular_price' => $isSale ? $product->price->max_price : $product->price->price,

            'on_sale' => $isSale,
            'in_stock' => true, //Сделать норм!

            'reviews_allowed' => true,
            'attributes' => $attributes,

//            "date_created" => "2017-03-23T00:53:11",
//            "date_created_gmt" => "2017-03-23T03:53:11",
//            "date_modified" => "2017-03-23T00:53:11",
//            "date_modified_gmt" => "2017-03-23T03:53:11",
            "sku" => "",

            "date_on_sale_from" => null,
            "date_on_sale_from_gmt" => null,
            "date_on_sale_to" => null,
            "date_on_sale_to_gmt" => null,
            "visible" => true,
            "purchasable" => true,
            "virtual" => false,
            "downloadable" => false,
            "downloads" => [],
            "download_limit" => -1,
            "download_expiry" => -1,
            "tax_status" => "taxable",
            "tax_class" => "",
            "manage_stock" => false,
            "stock_quantity" => null,
            "backorders" => "no",
            "backorders_allowed" => false,
            "backordered" => false,
            "weight" => "",

            "shipping_class" => "",
            "shipping_class_id" => 0,
//    "image" => {
//        "id" => 425,
//      "date_created" => "2016-10-19T12:21:16",
//      "date_created_gmt" => "2016-10-19T16:21:16",
//      "date_modified" => "2016-10-19T12:21:16",
//      "date_modified_gmt" => "2016-10-19T16:21:16",
//      "src" => "https://example.com/wp-content/uploads/2016/10/T_3_front-12.jpg",
//      "name" => "",
//      "alt" => "",
//      "position" => 0
//    },
//    "attributes" => [
//      {
//          "id" => 6,
//        "name" => "Color",
//        "option" => "Green"
//      }
//    ],
            "menu_order" => 0,
            "meta_data" => [],
        ];
    }

    /**
     * @param CmsContentElement|ShopContentElement: $product
     * @return array|boolean
     */
    public static function add($product)
    {
        $variation = self::getData($product);

        if (!$variation) {
            return false;
        }

        $mongoDB = \Yii::$app->mongodb->createCommand();

        $mongoDB->addUpdate(['id' => $variation['id']], $variation, ['upsert' => true]);

        return $mongoDB->executeBatch(Variation::collectionName());
    }

}