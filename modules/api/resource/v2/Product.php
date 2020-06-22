<?php


namespace modules\api\resource\v2;

use common\helpers\ArrayHelper;
use common\helpers\Image;
use common\helpers\Price;
use common\helpers\Url;
use common\models\CmsContentElement;
use common\models\Product AS ProductModel;
use common\models\Setting;
use common\thumbnails\Thumbnail;
use modules\shopandshow\models\common\StorageFile;
use yii\helpers\Html;
use frontend\assets\v3\AppAsset;

class Product extends ProductModel
{
    public function fields()
    {
        $w = 208;
        $h = 208;

        $previewW = 603;
        $previewH = 603;

        return [
            'id',
            'name' => function () {
                return $this->getLotName();
            },
            'brand' => function () {
                $brandData = [
                    'title' => '',
                    'image' => '',
                    'url' => '',
                ];

                if ($brand = $this->brand) {
                    $brandData = [
                        'title' => $brand->name,
                        'image' => $brand->getImageSrc() ? AppAsset::getAssetUrl($brand->getImageSrc()) : '',
                        'url' => $brand->url,
                    ];
                }

                return $brandData;
            },
            'category_id' => function () {
                return $this->lot->tree_id;
            },
            'categories' => function () {
                if ($tree = $this->lot->tree) {
                    $parents = $tree->parents;
                    $parents[] = $tree;
                    $categories = array_slice(ArrayHelper::getColumn($parents, 'name'), 2);
                }

                return join('/', $categories ?? []);
            },
            'google_category' => function () {
                return $this->lot->getGoogleCategoryName();
            },
            'image' => function () use ($w, $h) {
                $mainImageUrl = Image::getPhotoDefault();

                $productImage = null;

                if ($this->image) {
                    $productImage = $this->image;
                }else{
                    //* Учет кейса с карточкой без фото (базовой карточкой) *//
                    //Берем фото из лота
                    $lot = $this->lot;
                    if ($lot){
                        $productImage = $lot->image;
                    }
                    //* /Учет кейса с карточкой без фото (базовой карточкой) *//
                }

                if ($productImage) {
                    $mainImageUrl = \Yii::$app->imaging->thumbnailUrlSS($productImage->src,
                        new Thumbnail([
                            'w' => $w, // 220, // 218
                            'h' => $h, // 220, // 413
                        ]), $this->code
                    );
                }

                return $mainImageUrl;
            },
            'url' => function () {
                return $this->getUrl();
            },
            'thumbnails' => function () use ($w, $h) {
                $images = self::getImages($this->id);

                //* Учет кейса с карточкой без фото (базовой карточкой) *//
                if (!$images && !$this->image){
                    //Берем фото из лота
                    $images = self::getImages($this->parent_content_element_id);
                }
                //* /Учет кейса с карточкой без фото (базовой карточкой) *//

                $imagesUrls = [];
                if ($images) {
                    /** @var StorageFile $image */
                    foreach ($images as $image) {

                        $imageUrl = \Yii::$app->imaging->thumbnailUrlSS($image->src,
                            new Thumbnail([
                                'w' => $w, // 220, // 218
                                'h' => $h, // 220, // 413
                            ]), $this->code
                        );

                        $imagesUrls[] = $imageUrl;
                    }
                }

                return $imagesUrls;
            },
            //Все изображения так или иначе связанные с товаром (карточками, лотом)
            'images' => function() use ($previewW, $previewH){
                $result = [];
//                $cards = ProductModel::getProductCardsCanSaleQuery($this->id)->all(); //только доступные для продажи
                $cards = ProductModel::getProductCardsQuery($this->id)->onlyActive()->all(); //все кроме явно выключенных
//                if ($images = self::getImages($this->id)){

                $images = self::getImages(ArrayHelper::getColumn($cards, 'id'));

                //* Учет кейса с карточкой без фото (есть только базовая карточка) *//
                if ($cards && count($cards) == 1 && !$images){
                    //Берем фото из лота
                    $images = self::getImages($this->parent_content_element_id);
                }
                //* /Учет кейса с карточкой без фото (есть только базовая карточка) *//

                if ($cards && $images){
                    /** @var StorageFile $image */
                    foreach ($images as $image) {
                        $imageUrl = \Yii::$app->imaging->thumbnailUrlSS($image->src,
                            new Thumbnail([
                                'w' => $previewW,
                                'h' => $previewH,
                            ]), $this->code
                        );

                        $imageUrlPattern = str_ireplace(".{$image->extension}", '', $image->src);

                        $result[] = [
                            'id' => $image->id,
                            'src' => $imageUrl,
                            'pattern' => Url::withCdnPrefix($imageUrlPattern),
                            'name' => '',
                            'alt' => '',
                        ];
                    }
                }

                return $result;
            },
            '_price' => function () {
                return [
                    'current' => (string)((int)$this->new_price),
                    'old' => $this->hasDiscount() ? (string)intval($this->new_price_old) : null,
                ];
            },
            'price_type_id' => 'new_price_active',
            'price_label' => function (){
                return Price::getPriceLabel($this->new_price_active) ?: 'Цена со скидкой';
            },
            'price' => function () {
                return (string)intval($this->new_price);
            },
            'regular_price' => function () {
                //В случае когда есть скидка (цены не равны), важно проверить корректность соотношения
                return ( $this->new_price != $this->new_price_old && $this->hasDiscount() ) || ( $this->new_price == $this->new_price_old ) ? (string)intval($this->new_price_old) : '';
//                return (string)intval($this->new_price_old);
            },
            'sale_price' => function () {
                //Для фронта наличие сидочный цены означат показывать как кейс когда есть скидка
                //return $this->hasDiscount() ? (string)intval($this->new_price) : '';
                //$this->hasDiscount() - использовать нельзя ибо если цена без скидки меньше цены со скидкой то определится как скидки нет
                return $this->new_price != $this->new_price_old ? (string)intval($this->new_price) : '';
            },
            'prime_price' => function () {
                if (!Setting::getUsePricePrime()) {
                    return '';
                }
                return (int)($this->new_price && $this->pricePrime < $this->new_price ? $this->pricePrime : '');
            },
            'discount' => function () {
                $discount = null;

                if($this->hasDiscount()){
                    $discount = [
                        'type' => 'percent',
                        'amount' => (string)intval($this->new_discount_percent),
                    ];
                }
                return $discount;
            },
            'rating' => function () {
                return [
                    'value' => $this->new_rating ?: 4,
                    'max' => 5,
                    'step' => 1,
                ];
            },
            'badge' => function () {
                return null;
            },
            'attributes' => function () {

                $attrs = [];
                $attrId = 0;

                //* COLORS *//

                $colors = self::getLotColors($this->id, false);
//                $colors = self::getLotColors($this->id);
                if ($colors) {
                    $colorsList = [];
                    foreach ($colors as $cardId => $color) {
                        $images = ProductModel::getImages($cardId);

                        $colorData = [
                            'name' => Html::encode($color['name']),
                            'id' => (int)$color['id'],
                            'hex' => $color['hex'],
                            'images' => $images ? ArrayHelper::getColumn($images, 'id') : [],
                        ];

                        if (!empty($color['hexs']) && count($color['hexs']) > 1){
                            $colorData['set'] = $color['hexs'];
                        }

                        $colorsList[] = $colorData;
                    }

                    $attrs[] = [
                        'id' => 1,
                        'name' => 'Цвет',
                        'slug' => 'color',
//                        'options' => ArrayHelper::getColumn($colors, 'name'),
                        'options' => $colorsList,
                    ];
                }

                //* /COLORS *//

                //* SIZES *//

                $sizeScales = self::getLotSizeScales($this->id, false);
//                $sizeScales = self::getLotSizeScales($this->id);

                if ($sizeScales){
                    foreach ($sizeScales as $sizeScale) {
                        $sizesQuery = CmsContentElement::find()
                            ->select(['id', 'name', 'guid_id'])
                            ->orderBy('name')
                            ->where(['id' => explode(',', $sizeScale['property_values'])]);
                        $sizes = $sizesQuery->all();

//                        $sizesNames = $sizes ? ArrayHelper::getColumn($sizes, 'name') : [];
//                        sort($sizesNames);

                        $sizesOptions = [];
                        foreach ($sizes as $size) {
                            $sizesOptions[] = [
                                'name' => $size->name,
                                'id' => (int)$size->id,
                            ];
                        }

                        $attrs[] = [
                            'id' => (int)$sizeScale['id'],
                            'name' => 'Размер',
                            //'slug' => $sizeScale['code'],
                            'slug' => 'size', //больше одного быть не может, используем универсальный слаг
//                            'options' => $sizesNames,
                            'options' => $sizesOptions,
                        ];
                    }
                }

                //* /SIZES *//

                return $attrs;
            },
            'default_attributes' => function () {

                $attrs = [];

                if ($this->isCard()){
                    $lotColors = self::getLotColors($this->id, false);
//                    $lotColors = self::getLotColors($this->id);

                    if (!empty($lotColors[$this->id])) {
                        $cardColor = $lotColors[$this->id];
                        $attrs[] = [
                            'id' => 1,
                            'name' => 'Цвет',
                            'slug' => 'color',
                            'option' => [
                                'name' => Html::encode($cardColor['name']),
                                'id' => (int)$cardColor['id'],
                            ],
                        ];
                    }
                }

                //* SIZES *//

                $sizeScales = self::getLotSizeScales($this->id, false);
//                $sizeScales = self::getLotSizeScales($this->id);

                if ($sizeScales){
                    foreach ($sizeScales as $sizeScale) {
                        $sizesQuery = CmsContentElement::find()->select(['id', 'name', 'guid_id'])->where(['id' => explode(',', $sizeScale['property_values'])]);
                        $sizes = $sizesQuery->all();

                        //$sizesNames = $sizes ? ArrayHelper::getColumn($sizes, 'name') : [];

                        //if ($sizesNames && count($sizesNames) == 1){
                        if ($sizes && count($sizes) == 1){
                            $size = current($sizes);
                            $attrs[] = [
                                'id' => (int)$sizeScale['id'],
                                'name' => 'Размер',
                                //'slug' => $sizeScale['code'],
                                'slug' => 'size', //больше одного быть не может, используем универсальный слаг
                                'option' => [
                                    'name' => $size->name,
                                    'id' => (int)$size->id,
                                ]
                            ];
                        }
                    }
                }

                //* /SIZES *//

                return $attrs;
            },
            'variations' => function () {
//                $offers = self::getProductOffersCanSale($this->id);
                $offers = self::getProductOffersQuery($this->id)->onlyActive()->all();
                return $offers ? ArrayHelper::getColumn($offers, 'id') : null;
            }
        ];
    }
}
