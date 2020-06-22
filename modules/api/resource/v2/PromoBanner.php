<?php


namespace modules\api\resource\v2;


use common\models\Promo as PromoModel;

class PromoBanner extends PromoModel
{
    public function fields()
    {
        return [
            'id' => function () {
                return $this->id;
            },
            'name' => function () {
                return $this->name;
            },
            'description' => function () {
                return $this->description;
            },
            'url' => function () {
                return $this->getLink();
            },
            'banner' => function () {
                return $this->image_banner ? $this->getImageBanner() : '';
            }
        ];
    }
}