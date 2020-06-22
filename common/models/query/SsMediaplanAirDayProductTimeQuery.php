<?php

namespace common\models\query;

use common\helpers\Dates;

class SsMediaplanAirDayProductTimeQuery extends \common\models\generated\query\SsMediaplanAirDayProductTimeQuery
{
    public function byDay($time = null)
    {
        return $this
            ->andWhere(['>=', 'begin_datetime', Dates::beginOfDate($time)])
            ->andWhere(['<', 'begin_datetime', Dates::endOfDate($time)]);
    }

    public function byCategoryId($categoryId)
    {
        return $this->andWhere(['section_id' => $categoryId]);
    }

    public function byAirBlock($blockId)
    {
        return $this->andWhere(['block_id' => $blockId]);
    }

    public function byBeginDatetimePeriod($timeBegin, $timeEnd)
    {
        return $this
            ->andWhere(['>=', 'begin_datetime', $timeBegin])
            ->andWhere(['<', 'begin_datetime', $timeEnd]);
    }

    public function onAirNow()
    {
        return $this
            ->andWhere(['<=', 'begin_datetime', time()])
            ->andWhere(['>', 'end_datetime', time()]);
    }
}
