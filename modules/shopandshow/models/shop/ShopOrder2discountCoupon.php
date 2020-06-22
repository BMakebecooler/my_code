<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 21.06.17
 * Time: 12:44
 */

namespace modules\shopandshow\models\shop;


use skeeks\cms\shop\models\ShopOrder2discountCoupon as SxShopOrder2discountCoupon;

class ShopOrder2discountCoupon extends SxShopOrder2discountCoupon
{

    public function init()
    {
        parent::init();

        $this->on(self::EVENT_AFTER_INSERT, [$this, "_processAfterInsert"]);
//        $this->on(self::EVENT_AFTER_UPDATE, [$this, "_processAfterSave"]);
    }

    public function _processAfterInsert($e)
    {
        $this->discountCouponUpdate();
    }


    /**
     * Обновить данные после использования купона
     * @return bool
     */
    protected function discountCouponUpdate()
    {
        $discountCoupon = $this->discountCoupon;
        $discountCoupon->updateCounters(['use_count' => 1]);

        if ($discountCoupon->max_use > 0) {
            $discountCoupon->cms_user_id = $this->created_by;

            if ($discountCoupon->use_count >= $discountCoupon->max_use) {
                $discountCoupon->is_active = 0;
            }
        }

        return $discountCoupon->save();
    }


}