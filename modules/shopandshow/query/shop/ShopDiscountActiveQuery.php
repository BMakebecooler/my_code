<?php
namespace modules\shopandshow\query\shop;

use skeeks\cms\components\Cms;
use yii\db\ActiveQuery;

/**
 * Class ShopDiscountActiveQuery
 */
class ShopDiscountActiveQuery extends ActiveQuery
{
    public function active($state = true)
    {
        return $this
            ->andWhere(['active' => ($state == true ? Cms::BOOL_Y : Cms::BOOL_N)])
            ->andWhere(['OR',['<=', 'active_from', time()], ['active_from' => null]])
            ->andWhere(['OR',['>=', 'active_to', time()], ['active_to' => null]]);
    }

}