<?php

namespace modules\api\models\mongodb\product;

use common\helpers\ArrayHelper;
use common\lists\Contents;
use common\models\cmsContent\CmsContentElement;
use common\models\cmsContent\CmsContentProperty;
use common\thumbnails\Thumbnail;
use common\widgets\products\ModificationsWidget;
use modules\shopandshow\lists\Products;
use skeeks\cms\components\Cms;
use yii\helpers\Html;

class Product extends CommonProduct
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
        return 'products';
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

        //$attributes = Html::decode($product->relatedPropertiesModel->getAttribute('TECHNICAL_DETAILS'));
        $description = Html::decode(Products::getPropertyValue($product->id, 'PREIMUSHESTVA')); // $product->relatedPropertiesModel->getAttribute('PREIMUSHESTVA')
        //$komplektacia = Strings::bxHtml2br($model->relatedPropertiesModel->getAttribute('KOMPLEKTACIA'));

        $notPublic = Products::getPropertyValue($product->id, 'NOT_PUBLIC'); //$product->relatedPropertiesModel->getAttribute('NOT_PUBLIC');
        $isActive = $notPublic == Cms::BOOL_Y ? false : true; //TODO:: Добавить quantity

        $images = [];

        if ($product->image) {
            $images[] = [
                'src' => \Yii::$app->imaging->thumbnailUrlSS($product->image->src,
                    new Thumbnail([
                        'w' => 608, // 220, // 218
                        'h' => 608, // 220, // 413
                    ])
                )
            ];
        }

        foreach ($product->images as $image) {
            $images[] = [
                'src' => \Yii::$app->imaging->thumbnailUrlSS($image->src,
                    new Thumbnail([
                        'w' => 608, // 220, // 218
                        'h' => 608, // 220, // 413
                    ])
                )
            ];
        }

        $modifications = new ModificationsWidget([
            'namespace' => ModificationsWidget::getNameSpace(),
            'model' => $product,
        ]);

        $attributes = [];
        $defaultAttributes = [];

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
                'position' => 0,
                'visible' => true,
                'variation' => true,
            ];

            if ($property->code === ModificationsWidget::COLOR_CODE) {
                $attribute['name'] = 'Цвет';
            }

            foreach ($optionsData as $item) {
                $title = ((bool)$item['quantity']) ? $item['name'] : 'Нет в наличии';
                $attribute['options'][] = $title;
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


            if (empty($defaultAttributes[$property->id])) {
                $defaultAttributes[$property->id] = [
                    'id' => $property->id,
                    'name' => $property->getItemName(),
                    'option' => reset($attribute['options']),
                ];
            }

            $attributes[] = $attribute;
        }

        unset($modifications);

        $variations = [];
        $statistic = [];

        if ($product->isLot()) {
            $variations = Contents::getChildrensContentElementIds($product->id);
            $statistic = ($statistic = Products::getStatistic($product->id)) ? ArrayHelper::arrayToFloat($statistic) : [];
        }

        $isSale = $product->price->price < $product->price->max_price && (self::$basePriceId != $product->price->type_price_id);

        return [
            'id' => $product->id,
            'name' => $product->name,
            'bid' => $product->bitrix_id,
            'guid' => $product->getGuid(),
            'active' => $isActive,
            'category_id' => $product->tree_id,
            'category_name' => ($product->cmsTree) ? $product->cmsTree->name : '',
            'slug' => $product->code,
            'permalink' => $product->absoluteUrl,
            'images' => $images,

            'description' => $description, //$product->description_full, // TODO вынести в attributes
            //'attributes' => [['name' => 'Характеристики', 'options' => [$attributes]]], // TODO - должно быть массивом @see http://woocommerce.github.io/woocommerce-rest-api-docs/#product-properties
            //'sku' => null,
            'link' => $product->absoluteUrl, // ?? нет такого свойства в api // TODO потом удалить

            'in_stock' => true, //TODO::Сделать норм!
//
            'price' => $product->price->price,
            'regular_price' => $isSale ? $product->price->max_price : $product->price->price,
            'on_sale' => $isSale,

            'reviews_allowed' => true,
            'average_rating' => Products::getPropertyValue($product->id, 'RATING'),
            'rating_count' => 0, // TODO

            'attributes' => $attributes,
            'variations' => $variations,
            'default_attributes' => array_values($defaultAttributes),
            'statistic' => $statistic
        ];
    }

    /**
     * @param CmsContentElement|ShopContentElement: $product
     * @return array|boolean
     */
    public static function add($product)
    {
        $productInfo = self::getData($product);

        if (!$productInfo) {
            return false;
        }

        $mongoDB = \Yii::$app->mongodb->createCommand();

        $mongoDB->addUpdate(['id' => $productInfo['id']], $productInfo, ['upsert' => true]);

        return $mongoDB->executeBatch(Product::collectionName());
    }


}