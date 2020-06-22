<?php

/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 30.08.17
 * Time: 17:37
 */

namespace modules\shopandshow\lists;


use modules\shopandshow\models\common\Guid;
use modules\shopandshow\models\shop\ShopOrder;

class Orders
{


    /**
     * @param $guid
     * @return ShopOrder|null|\yii\db\ActiveRecord
     */
    public static function getOrderByGuid($guid)
    {
        $order = ShopOrder::find();
        $order->innerJoin('ss_guids', 'ss_guids.id = shop_order.guid_id');
        $order->andWhere('ss_guids.guid = :guid AND ss_guids.entity_type = :entity_type', [
            ':guid' => $guid,
            ':entity_type' => Guid::ENTITY_TYPE_ORDER
        ]);

        return $order->one();
    }

}