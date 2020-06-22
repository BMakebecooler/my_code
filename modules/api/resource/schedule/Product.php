<?php


namespace modules\api\resource\schedule;

use common\helpers\Image;
use common\helpers\Size;
use common\models\Product AS ProductModel;
use common\thumbnails\Thumbnail;
use modules\shopandshow\models\common\StorageFile;

class Product extends ProductModel
{
    public function fields()
    {
        $w = 208;
        $h = 208;

        return [
            'id',
            'name',
            'image' => function () use ($w, $h) {
                $mainImageUrl = Image::getPhotoDefault();
                if ($this->image) {
                    $mainImageUrl = \Yii::$app->imaging->thumbnailUrlSS($this->image->src,
                        new Thumbnail([
                            'w' => $w, // 220, // 218
                            'h' => $h, // 220, // 413
                        ]), $this->code
                    );
                }

                return $mainImageUrl;
            },
            'url' => function () {
                return "/products/{$this->id}-{$this->code}/";
            },
            'thumbnails' => function () use ($w, $h) {
                $images = $this->getImages($this->id);

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
            'price' => function () {
                return [
                    'current' => (int)$this->new_price,
                    'old' => $this->hasDiscount() ? (int)$this->new_price_old : null,
                ];
            },
            'discount' => function () {
                return $this->hasDiscount() ? (int)$this->new_discount_percent : null;
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
                return [
                        [
                            'id' => 0,
                            'name' => 'Размер',
                            'options' => Size::getLotSizes($this->id)
                        ]
                ];
            }
        ];
    }
}