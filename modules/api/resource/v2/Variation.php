<?php


namespace modules\api\resource\v2;

use common\helpers\ArrayHelper;
use common\helpers\Image;
use common\helpers\Price;
use common\helpers\Property;
use common\helpers\Size;
use common\models\CmsContentElement;
use common\models\CmsContentProperty;
use common\models\ProductProperty;
use common\models\Product AS ProductModel;
use common\models\Setting;
use common\thumbnails\Thumbnail;
use modules\shopandshow\models\common\StorageFile;
use yii\helpers\Html;

class Variation extends ProductModel
{
    public function fields()
    {
        $w = 208;
        $h = 208;

        $card = Product::findOne($this->parent_content_element_id);

        return [
            'id',
            'name',
            'image' => function () use ($card, $w, $h) {
                $imgId = '';
                $mainImageUrl = Image::getPhotoDefault();
                if ($card->image) {
                    $mainImageUrl = \Yii::$app->imaging->thumbnailUrlSS($card->image->src,
                        new Thumbnail([
                            'w' => $w, // 220, // 218
                            'h' => $h, // 220, // 413
                        ]), $this->code
                    );
                    $imgId = $card->image->id;
                }

                return [ //Для модификации берем фотки берем из родителя
                    'id' => $imgId,
                    'src' => $mainImageUrl,
                    'name' => '',
                    'alt' => '',
                ];
            },
            'permalink' => function () {
                $product = self::getLot($this->id);
                $lotNum = $product ? $product->code : $this->code;

                return "/products/{$this->parent_content_element_id}-{$lotNum}/"; //ссылка на карточку
            },
            'stock_quantity' => 'new_quantity',
            'stock_status' => function () {
                return $this->new_quantity ? 'instock' : 'outofstock';
            },
            '_price' => function () {
                return [
                    'current' => (string)intval($this->new_price),
                    'old' => $this->hasDiscount() ? (string)intval($this->new_price_old) : null,
                ];
            },
            'price_type_id' => 'new_price_active',
            'price_label' => function (){
                return Price::getPriceLabel($this->new_price_active) ?: 'Цена со скидкой';
            },
            'price' => function () {return (string)intval($this->new_price); },
            'regular_price' => function () {
                return ( $this->new_price != $this->new_price_old && $this->hasDiscount() ) || ( $this->new_price == $this->new_price_old ) ? (string)intval($this->new_price_old) : '';
//                return (string)intval($this->new_price_old);
            },
            'sale_price' => function () {
                //Для фронта наличие сидочный цены означат показывать как кейс когда есть скидка
                return $this->new_price != $this->new_price_old ? (string)intval($this->new_price) : '';
//                return $this->hasDiscount() ? (string)intval($this->new_price) : '';
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
            'badge' => function () {
                return null;
            },
            'attributes' => function () use ($card) {
                $attrs = [];

                //* COLORS *//

                $lotColors = self::getLotColors($this->id, false);

                if (!empty($lotColors[$card->id])) {
                    $cardColor = $lotColors[$card->id];
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

                //* /COLORS *//

                //* SIZES *//

                $offerSizes = self::getSizesFromProps($this->id);

                if ($offerSizes){
                    foreach ($offerSizes as $size) {
                        $attrs[] = [
                            'id' => (int)$size['property_id'],
                            'name' => 'Размер',
//                            'alias' => $size['name'],
                            'slug' => 'size',
                            'option' => [
                                'name' => $size['name'],
                                'id' => (int)$size['id'],
                            ]
                        ];
                    }
                }

                //* /SIZES *//

                return $attrs;
            }
        ];
    }
}
