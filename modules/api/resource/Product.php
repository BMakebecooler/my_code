<?php

namespace modules\api\resource;

use common\helpers\Color;
use skeeks\cms\mail\helpers\Html;
use common\lists\Contents;
use common\models\cmsContent\CmsContentProperty;
use common\thumbnails\Thumbnail;
use common\widgets\products\ModificationsWidget;
use modules\shopandshow\lists\Products;
use skeeks\cms\components\Cms;

/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 26/12/2018
 * Time: 15:17
 */
class Product extends Resourse
{

    public $isActive;
    public $isSale;
    public $defaultAttributes;
    public $statisticData;
    public $variations;
    public $attributes;
    public $description;
    public $specifications;
    public $imagesData = [];

    public function fields()
    {
        $product = $this;

        //$this->attributes = Html::decode($product->relatedPropertiesModel->getAttribute('TECHNICAL_DETAILS'));
        $this->description = Html::decode(Products::getPropertyValue($product->id, 'PREIMUSHESTVA')); // $product->relatedPropertiesModel->getAttribute('PREIMUSHESTVA')
        $this->specifications = Html::decode(Products::getPropertyValue($product->id, 'HARAKTERISTIKI')); // $product->relatedPropertiesModel->getAttribute('PREIMUSHESTVA')
        //$komplektacia = Strings::bxHtml2br($model->relatedPropertiesModel->getAttribute('KOMPLEKTACIA'));

        $notPublic = Products::getPropertyValue($product->id, 'NOT_PUBLIC'); //$product->relatedPropertiesModel->getAttribute('NOT_PUBLIC');
        $this->isActive = $notPublic == Cms::BOOL_Y ? false : true; //TODO:: Добавить quantity

        $imagesData = [];
        if ($product->image) {
            $imagesData[] = [
                'src' => \Yii::$app->imaging->thumbnailUrlSS($product->image->src,
                    new Thumbnail([
                        'w' => 608, // 220, // 218
                        'h' => 608, // 220, // 413
                    ])
                )
            ];
        }

        foreach ($product->images as $image) {
            $imagesData[] = [
                'src' => \Yii::$app->imaging->thumbnailUrlSS($image->src,
                    new Thumbnail([
                        'w' => 608, // 220, // 218
                        'h' => 608, // 220, // 413
                    ])
                )
            ];
        }

        $this->imagesData = $imagesData;
        $modifications = new ModificationsWidget([
            'namespace' => ModificationsWidget::getNameSpace(),
            'model' => $product,
        ]);

        $this->attributes = [];
        $this->defaultAttributes = [];

        $position = 0;
        /**
         * @var $property CmsContentProperty
         */
        foreach ($modifications->getParameters() as $key => $property) {

            $optionsData = $modifications->getParameterData($property->code);

            if (!$optionsData) {
                continue;
            }

            $attribute = [
                'id' => $property->id,
                'name' => $property->getItemName(),
                'position' => ($position++),
                'visible' => true,
                'variation' => true,
            ];

            if ($property->code === ModificationsWidget::COLOR_CODE) {
                $attribute['name'] = 'Цвет';
            }

            foreach ($optionsData as $item) {
                $title = ((bool)$item['quantity']) ? $item['name'] : 'Нет в наличии';
                // 1027299 color
//                $title = $attribute['id'] == ModificationsWidget::KFSS_COLOR_ID ? Color::getHexFromName($title) : $title;
                if ($title == false) {
                    $title = '#333333';
                }
                $attribute['options'][] = $title;
                $attribute['alias'] = $attribute['id'] == ModificationsWidget::KFSS_COLOR_ID ? 'color' : 'other';

                // change attributes value
//                $attribute['title'] = $attribute['name'];
//                $attribute['name'] = $attribute['alias'];
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


            if (empty($this->defaultAttributes[$property->id])) {
                $this->defaultAttributes[$property->id] = [
                    'id' => $property->id,
                    'name' => $property->getItemName(),
                    'option' => reset($attribute['options']),
                ];
            }

            $this->attributes[] = $attribute;
        }

        unset($modifications);

        $this->variations = [];

        if ($product->isLot()) {
            $this->variations = Contents::getChildrensContentElementIds($product->id);
        }

        $this->isSale = ($product->price) && $product->price->price < $product->price->max_price &&
            (self::$basePriceId != $product->price->type_price_id);

        return [
            'id' => function (self $product) {
                return $product->id;
            },
            'name' => function (self $product) {
                return $product->name;
            },
            'bid' => function (self $product) {
                return $product->bitrix_id;
            },
//            'guid' => function (self $product) {
//                return $product->getGuid();
//            },
//            'active' => function () {
//                return $this->isActive;
//            },
//            'category_id' => function (self $product) {
//                return $product->tree_id;
//            },
//            'category_name' => function (self $product) {
//                return ($product->cmsTree) ? $product->cmsTree->name : '';
//            },
            'slug' => function (self $product) {
                return $product->code;
            },
            'permalink' => function (self $product) {
                return $product->absoluteUrl;
            },
            'images' => function () {
                return $this->imagesData;
            },
//
            'description' => function () {
                return $this->description;
            },
            'specifications' => function () {
                return $this->specifications;
            },
            //$product->description_full;}, // TODO вынести в attributes
//            //'attributes' => [['name' => 'Характеристики';}, 'options' => [$this->attributes]]];}, // TODO - должно быть массивом @see http://woocommerce.github.io/woocommerce-rest-api-docs/#product-properties
//            //'sku' => null;},
            'link' => function (self $product) {
                return $product->absoluteUrl;
            }, // ?? нет такого свойства в api // TODO потом удалить

            'in_stock' => function () {
                return true;
            }, //TODO::Сделать норм!
////
            'price' => function (self $product) {
                return $product->price ? $product->price->price : 0;
            },
            'regular_price' => function (self $product) {
                if ($this->price) {
                    return $this->isSale ? $product->price->max_price : $product->price->price;
                }

                return 0;
            },
            'on_sale' => function () {
                return $this->isSale;
            },

            'reviews_allowed' => function (self $product) {
                return true;
            },
            'average_rating' => function (self $product) {
                return Products::getPropertyValue($product->id, 'RATING');
            },
            'rating_count' => function (self $product) {
                return 0;
            }, // TODO

            'attributes' => function () {
                return $this->attributes;
            },
            'variations' => function () {
                return $this->variations;
            },
            'type' => function () {
                return 'variable';
            },
            'status' => function () {
                return 'publish';
            },
            'featured' => function () {
                return  false;
            },
            'purchasable' => function () {
                return  true;
            },
            'catalog_visibility' => function () {
                return  'visible';
            },
            'default_attributes' => function () {
                return array_values($this->defaultAttributes);
            },
        ];
    }
}