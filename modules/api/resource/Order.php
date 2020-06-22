<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 19/02/2019
 * Time: 17:29
 */

namespace modules\api\resource;


use modules\shopandshow\models\shop\ShopOrder;

class Order extends ShopOrder
{

    public function fields()
    {
        return [
            'id',
            'status_code',
        ];
    }

}