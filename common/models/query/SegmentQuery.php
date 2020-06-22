<?php


namespace common\models\query;


class SegmentQuery extends \common\models\generated\query\SegmentQuery
{
    public function notDisabled()
    {
        return $this->andWhere(['!=', 'disabled', \common\helpers\Common::BOOL_Y_INT]);
    }

    public function disabled()
    {
        return $this->andWhere(['disabled' => \common\helpers\Common::BOOL_Y_INT]);
    }
}