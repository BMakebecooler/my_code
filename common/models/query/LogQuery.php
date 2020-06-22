<?php


namespace common\models\query;


class LogQuery extends \common\models\generated\query\LogQuery
{
    public function byModelClass(string $modelClass)
    {
        return $this->andWhere(['model_class' => $modelClass]);
    }

    public function byType(string $type)
    {
        return $this->andWhere(['type' => $type]);
    }
}