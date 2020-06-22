<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 15/03/2019
 * Time: 16:25
 */

namespace common\models\query;


class ShopOrderQuery extends \common\models\generated\query\ShopOrderQuery
{

    public function today()
    {
        $dateTime = new \DateTime();
        $dateTime->setTime(00, 00, 00);
//        $dateTime->modify('-101 day');
        return $this->andWhere(['>', 'created_at', $dateTime->getTimestamp()]);
    }
    public function byStatusCode($code)
    {
        return $this->andWhere(['status_code' => $code]);
    }

}