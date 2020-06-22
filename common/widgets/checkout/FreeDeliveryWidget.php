<?php

namespace common\widgets\checkout;

use modules\shopandshow\models\shop\delivery\FreeDelivery;
use modules\shopandshow\models\shop\delivery\FreeDeliveryFactory;
use modules\shopandshow\models\shop\ShopDiscount;
use skeeks\cms\base\Widget;

class FreeDeliveryWidget extends Widget
{

    public $viewFile = '@template/widgets/Checkout/free_delivery';

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render($this->viewFile);
    }

    /**
     * Активна ли Бесплатная доставка
     * @return ShopDiscount|null
     */
    public static function getFreeDeliveryDiscount()
    {
        $ssShopFuserDiscount = \Yii::$app->shop->shopFuser->ssShopFuserDiscount;
        if ($ssShopFuserDiscount) {
            return $ssShopFuserDiscount->freeDeliveryDiscount;
        }
        return null;
    }

    /**
     * Возвращает список доступных акций с БД
     * @return FreeDelivery[]
     */
    public function getFreeDeliveries()
    {
        $freeDeliveries = FreeDeliveryFactory::create();

        return $freeDeliveries;
    }
}