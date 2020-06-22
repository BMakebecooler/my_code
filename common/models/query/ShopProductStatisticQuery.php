<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-03-25
 * Time: 13:41
 */

namespace common\models\query;


use common\models\ShopProductStatistic;

class ShopProductStatisticQuery extends \common\models\generated\query\ShopProductStatisticQuery
{
    public function onlyBestseller()
    {
        return $this->andWhere([
            '>=', 'ordered', ShopProductStatistic::MIN_ORDERED_FOR_BESTSELLER_BADGE
        ]);
    }
}