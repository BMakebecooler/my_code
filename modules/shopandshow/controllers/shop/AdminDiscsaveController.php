<?php
namespace modules\shopandshow\controllers\shop;

use \skeeks\cms\shop\controllers\AdminDiscsaveController as SXAdminDiscsaveController;
use modules\shopandshow\models\shop\ShopDiscount;


/**
 * Class AdminDiscountCouponController
 * @package modules\shopandshow\controllers
 */
class AdminDiscsaveController extends SXAdminDiscsaveController
{
    public function init()
    {
        parent::init();

        $this->modelClassName           = ShopDiscount::className();
    }
}
