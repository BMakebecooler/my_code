<?php

namespace modules\api\resource;

use common\models\cmsContent\CmsContentElement;
use modules\shopandshow\models\mediaplan\AirBlock;

class MPAairBlock extends AirBlock
{
    public function fields()
    {
        return [
            'id',
            'name' => function () {
                return $this->getCategoryName();
            },
            'begin_datetime',
            'end_datetime',
            'current' => function () {
                $currentTime = time();
                if ($this->begin_datetime <= $currentTime && $this->end_datetime >= $currentTime) {
                    return true;
                }

                return false;
            },
            'time' => function () {
                return sprintf('%s - %s', date('H:00', $this->begin_datetime), date('H:00', $this->end_datetime));
            },

            'block_id',
            'products' => function () {
                return CmsContentElement::getDb()->cache(function () {
                    return $this->getCmsContentElements()
                        ->joinWith(['images'])->andWhere('images.id IS NOT NULL')->limit(15)->all();
                }, MIN_5);
            },
        ];
    }

    public function getCmsContentElements()
    {
        return $this->hasMany(Product::className(), ['id' => 'lot_id'])
            ->andWhere(['cms_content_element.content_id' => PRODUCT_CONTENT_ID])
            ->via('airDayProductTime');
    }
}