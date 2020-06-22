<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-04-11
 * Time: 15:39
 */

namespace common\models\query;


class BuhECommAbcQuery extends \common\models\generated\query\BuhECommAbcQuery
{

    public function byType($id){
        return $this->andWhere(['type_id' => $id]);
    }
}