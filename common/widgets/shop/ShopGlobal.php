<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 26.05.17
 * Time: 16:27
 */

namespace common\widgets\shop;

use common\helpers\ArrayHelper;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\shop\widgets\ShopGlobalWidget;

class ShopGlobal extends ShopGlobalWidget
{

    /**
     * @return array
     */
    public function baseClientOptions()
    {
        $result = ArrayHelper::merge(parent::baseClientOptions(), [
            'backend-add-product' => UrlHelper::construct('shopandshow/cart/add-product')->toString(),
            'backend-add-offer-product' => UrlHelper::construct('shopandshow/cart/add-offer-product')->toString(),
            'backend-has-removed' => UrlHelper::construct('shopandshow/cart/has-removed')->toString(),
            'backend-add-discount-coupon' => UrlHelper::construct('shopandshow/cart/add-discount-coupon')->toString(),
            'backend-remove-discount-coupon' => UrlHelper::construct('shopandshow/cart/remove-discount-coupon')->toString(),
            'backend-update-basket' => UrlHelper::construct('shopandshow/cart/update-basket')->toString(),
            'backend-delivery-calculate' => UrlHelper::construct('shopandshow/cart/delivery-calc')->toString(),
            'backend-set-delivery' => UrlHelper::construct('shopandshow/cart/set-delivery')->toString(),
            'backend-set-shop-delivery' => UrlHelper::construct('shopandshow/cart/set-shop-delivery')->toString(),
        ]);

        return $result;
    }

    public function run()
    {
        return parent::run();
    }
}