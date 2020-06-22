<?php


namespace modules\api\resource\v2;

use common\models\Promo AS PromoModel;

class Promo extends PromoModel
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
                //TODO добавить комппонгент yii2-file-kit
//                $attachment =  $this->getPromoAttachment()->one();
//                if($attachment) {
//                    return $attachment->base_url.'/'.$attachment->path;
//                }else{
//                    return null;
//                }
                if ($this->image_banner) {
                    return $this->getImageBanner();
                } else {
                    return null;
                }


            }
        ];
    }
}