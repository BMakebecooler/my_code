<?php

namespace modules\api\resource;

use common\helpers\User;
use common\lists\Contents;
use common\thumbnails\Thumbnail;
use common\widgets\products\ModificationsWidget;
use modules\shopandshow\models\common\StorageFile;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsContentElementProperty;

/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 26/12/2018
 * Time: 15:17
 */
class Variation extends Resourse
{

//    public $isActive;
    public $cartImage = [];
    public $isSale;
    public $description;
    public $specifications;
    public $product_id;

    public $price;
    public $max_price;
    public $cart_image_id;
    public $card_id;
    public $properties_id;
    public $type_price_id;
    public $quantity;

    public $variations = [];


    public function fields()
    {
        $product = $this;

        //$this->attributes = Html::decode($product->relatedPropertiesModel->getAttribute('TECHNICAL_DETAILS'));
//        $this->description = Html::decode(Products::getPropertyValue($product->id, 'PREIMUSHESTVA')); // $product->relatedPropertiesModel->getAttribute('PREIMUSHESTVA')
//        $this->specifications = Html::decode(Products::getPropertyValue($product->id, 'HARAKTERISTIKI')); // $product->relatedPropertiesModel->getAttribute('PREIMUSHESTVA')
        //$komplektacia = Strings::bxHtml2br($model->relatedPropertiesModel->getAttribute('KOMPLEKTACIA'));

//        $notPublic = Products::getPropertyValue($product->id, 'NOT_PUBLIC'); //$product->relatedPropertiesModel->getAttribute('NOT_PUBLIC');
//        $this->isActive = $notPublic == Cms::BOOL_Y ? false : true; //TODO:: Добавить quantity

        if ($product->cart_image_id) {
            $image = StorageFile::findOne($product->cart_image_id);
            $this->cartImage = [
                'src' => \Yii::$app->imaging->thumbnailUrlSS($image->src,
                    new Thumbnail([
                        'w' => 608, // 220, // 218
                        'h' => 608, // 220, // 413
                    ])
                )
            ];
        }

        foreach (explode(',', $product->properties_id) as $propertyId) {
            $propertyValue = CmsContentElementProperty::find()->alias('value')->joinWith(['property'])
                ->andWhere('value.id = :id', [':id' => $propertyId])->one();
            $property = $propertyValue->property;

            $isColor = $property->code === ModificationsWidget::COLOR_CODE;

            if ($valueDict = Contents::getContentElementById($propertyValue->value_enum)) {
                $title = $valueDict->name;

                if ($isColor) {
                    /**
                     * @todo Сделать в 1 запрос
                     */
                    $kfssColorBxProperty = CmsContentElementProperty::find()->alias('value')
                        ->andWhere('value.element_id = :id AND value.property_id = :property_id', [
                            ':id' => $this->card_id,
                            ':property_id' => ModificationsWidget::COLOR_CODE_BX_ID,
                        ])->one();

                    if ($kfssColorBxProperty) {
                        $valueDictBxColor = Contents::getContentElementById($kfssColorBxProperty->value_enum);
                        $valueDictBxColor ? ($title .= ', ' . $valueDictBxColor->name) : null;
                    }
                }
            } else {
                $title = $propertyValue->value;
            }

            $attribute = [
                'id' => $property->id,
                'name' => $property->name,
            ];

            if ($isColor) {
                $attribute['name'] = 'Цвет';
            }

            // 1027299 color
//            $title = $property->id == ModificationsWidget::KFSS_COLOR_ID ? Color::getHexFromName($title) : $title;
            if ($title == false) {
                $title = '#333333';
            }

            $attribute['option'] = $title;
            $attribute['alias'] = $isColor ? 'color' : 'other';

            $this->variations[] = $attribute;

        }

        $this->isSale = ($product->price) && $product->price < $product->max_price &&
            (self::$basePriceId != $product->type_price_id);

        return [


//        "date_created": "2019-01-03T11:52:13",
//        "date_created_gmt": "2019-01-03T11:52:13",
//        "date_modified": "2019-01-03T11:52:13",
//        "date_modified_gmt": "2019-01-03T11:52:13",
//        "permalink": "http://mstore.local/product/stitch-detail-tunic-dress/?attribute_pa_color=yellow&attribute_pa_size=xl",


            'id' => function (self $product) {
                return $product->id;
            },
//            'product_id' => function (self $product) {
//                return $product->product_id;
//            },
//            'bid' => function (self $product) {
//                return $product->bitrix_id;
//            },
            'name' => function (self $product) {
                return $product->name;
            },
            'card_id' => function (self $product) {
                return $product->card_id;
            },
            'date_created' => function (self $product) {
                return date('Y-m-d H:i:s', $product->created_at);
            },
            'date_modified' => function (self $product) {
                return date('Y-m-d H:i:s', $product->created_at);
            },
//            'active' => function (self $product) {
//                return $product->active == Cms::BOOL_Y ? true : false;
//            },

//            'category_id' => $product->tree_id,
//            'category_name' => $product->cmsTree->name,
//            'slug' => $product->code,

//            'permalink' => $product->absoluteUrl,
            'description' => function (self $product) {
                return $product->description;
            },
            'price' => function (self $product) {
                return (string)$product->price;
            },
            'sale_price' => function (self $product) {
                return (string)$product->price;
            },
            'regular_price' => function (self $product) {
                if ($this->price) {
                    return (string)($this->isSale ? $product->max_price : $product->price);
                }

                return 0;
            },
            'on_sale' => function (self $product) {
                return $product->isSale;
            },
            'attributes' => function (self $product) {
                return $product->variations;
            },
            'image' => function (self $product) {
                return $this->cartImage;
            },
            'purchasable' => function (self $product) {
                return (bool)$this->quantity;
            },
            'in_stock' => function (self $product) {
                return (bool)$this->quantity;
            },
            'reviews_allowed' => function (self $product) {
                return true; //Сделать норм!
            },
            'sku' => function (self $product) {
                return ''; //Сделать норм!
            },
            'date_on_sale_from' => function (self $product) {
                return null;
            },
            'date_on_sale_from_gmt' => function (self $product) {
                return null;
            },
            'date_on_sale_to' => function (self $product) {
                return null;
            },
            'date_on_sale_to_gmt' => function (self $product) {
                return null;
            },
            'visible' => function (self $product) {
                return true;
            },

            'virtual' => function (self $product) {
                return false;
            },
            'downloadable' => function (self $product) {
                return false;
            },
            'downloads' => function (self $product) {
                return [];
            },
            'download_limit' => function (self $product) {
                return -1;
            },
            'download_expiry' => function (self $product) {
                return -1;
            },
            'tax_status' => function (self $product) {
                return 'taxable';
            },

            'tax_class' => function (self $product) {
                return '';
            },
            'manage_stock' => function (self $product) {
                return false;
            },
            'stock_quantity' => function (self $product) {
                return null;
            },
            'backorders' => function (self $product) {
                return 'no';
            },
            'backorders_allowed' => function (self $product) {
                return false;
            },
            'backordered' => function (self $product) {
                return false;
            },
            'weight' => function (self $product) {
                return '';
            },
            'shipping_class' => function (self $product) {
                return '';
            },
            'shipping_class_id' => function (self $product) {
                return 0;
            },

            'menu_order' => function (self $product) {
                return 0;
            },
            'meta_data' => function (self $product) {
                return [];
            },
        ];
    }
}