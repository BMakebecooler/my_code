<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-05-06
 * Time: 13:08
 */

namespace common\models\query;


class ProductAbcAdditionQuery extends \common\models\generated\query\ProductAbcAdditionQuery
{
    public function bySourceId($id)
    {
        return $this->andWhere(['source_id' => $id]);
    }

}



