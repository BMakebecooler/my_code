<?php

namespace common\components\mongo;

use yii\mongodb\Query as MongoQuery;

class Query extends MongoQuery
{

    public function active($state = true, $conditionName = 'active')
    {
        return $this->andWhere([$conditionName => $state]);
    }

    public function def($state = true)
    {
        return $this->andWhere(['def' => $state]);
    }

}