<?php

namespace common\models\query;

use common\helpers\Dates;

class SsMediaplanAirBlocksQuery extends \common\models\generated\query\SsMediaplanAirBlocksQuery
{
    public function byDay($time = null)
    {
        $time = $time ?? Time();

        return $this
            ->andWhere(['>=', 'begin_datetime', Dates::beginOfDate($time)])
            ->andWhere(['<', 'begin_datetime', Dates::endOfDate($time)]);
    }

    public function byCategoryId($categoryId)
    {
        return $this->andWhere(['section_id' => $categoryId]);
    }

    public function byBeginDatetime($time = null)
    {
        return $this->andWhere(['begin_datetime' => $time ?? time()]);
    }
}
