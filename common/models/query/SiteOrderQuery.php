<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-08-05
 * Time: 12:55
 */

namespace common\models\query;


class SiteOrderQuery extends \common\models\generated\query\SiteOrderQuery
{


    public function byOrderId($id){
        return $this->andWhere(['order_id' => $id]);
    }
}