<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-04-19
 * Time: 16:57
 */

namespace common\models\query;


class ProductAbcQuery extends \common\models\generated\query\ProductAbcQuery
{


    public function byType($id){
        return $this->andWhere(['type_id' => $id]);
    }
}