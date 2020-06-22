<?php
namespace modules\shopandshow\controllers\shop;

use \skeeks\cms\shop\controllers\AdminDiscountCouponController as SXAdminDiscountCouponController;
use modules\shopandshow\models\shop\ShopDiscountCoupon;

/**
 * Class AdminDiscountCouponController
 * @package modules\shopandshow\controllers
 */
class AdminDiscountCouponController extends SXAdminDiscountCouponController
{
    public function init()
    {
        parent::init();

        $this->modelClassName           = ShopDiscountCoupon::className();
    }
}
