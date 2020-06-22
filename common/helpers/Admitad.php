<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 05/03/2019
 * Time: 19:25
 */

namespace common\helpers;


use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopOrderStatus;

class Admitad
{


    public static function getStatusFromOrder(ShopOrder $shopOrder){
        if($shopOrder->status_code == ShopOrderStatus::STATUS_CANCELED){
            return 2;
        }
        if($shopOrder->status_code == ShopOrderStatus::STATUS_COMPLETED){
            return 1;
        }

        return 0;
    }
}