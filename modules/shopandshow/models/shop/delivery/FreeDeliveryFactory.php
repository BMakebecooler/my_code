<?php
namespace modules\shopandshow\models\shop\delivery;

use modules\shopandshow\models\shop\ShopDiscount;

class FreeDeliveryFactory
{
    public static function create()
    {
        /** @var FreeDelivery[] $result */
        $result = [];
        /** @var FreeDelivery $globalDiscount */
        $globalDiscount = null;

        /** @var ShopDiscount[] $shopDiscounts */
        $shopDiscounts = ShopDiscount::find()
            ->active()
            ->andWhere(['value_type' => ShopDiscount::VALUE_TYPE_DELIVERY])
            ->indexBy('id')
            ->all();

        foreach ($shopDiscounts as $shopDiscount) {
            $freeDelivery = new FreeDelivery($shopDiscount);
            // акция без условий может быть только одна, ищем с минимальной суммой
            if ($freeDelivery->isCommon()) {
                if (empty($globalDiscount) || $freeDelivery->getSum() < $globalDiscount->getSum()) {
                    $globalDiscount = $freeDelivery;
                }
            }
            else {
                // все остальные акции на БД
                $result[] = $freeDelivery;
            }
        }

        if ($globalDiscount) {
            // Если глобальная акция в итоге оказалась выгодней акции с условиями, то акции с условиями убираем
            foreach ($result as $i => $freeDelivery) {
                if ($globalDiscount->getRemainSum() <= $freeDelivery->getRemainSum()) {
                    unset($result[$i]);
                }
            }
            $result[] = $globalDiscount;
        }

        return $result;
    }
}