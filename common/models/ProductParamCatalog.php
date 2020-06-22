<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-03-27
 * Time: 16:35
 */

namespace common\models;


class ProductParamCatalog extends \common\models\generated\models\ProductParamCatalog
{
    public function getPrice()
    {
        return $this->price;
    }

    public function getLotName()
    {
        return $this->name;
    }

    public function isBadgeNew()
    {
        return false;
    }

    public function isRedBadge()
    {
        return false;
    }

    public function isDiscount()
    {
        return $this->discount > 0 ? true : false;
    }

    public function getPriceOld()
    {
        return $this->price_old;
    }

    public function getRating()
    {
        return $this->rating;
    }

    public function badgeDiscount()
    {
        return $this->discount;
    }

    public function getUrl()
    {
        return "products/{$this->product_id}-{$this->lot_num}/";
    }

}