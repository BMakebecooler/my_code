<?php

/**
 * @author Arkhipov Andrei <arhan89@gmail.com>
 * @copyright (c) K-Gorod
 * Date: 05.04.2019
 * Time: 15:33
 */

namespace common\models\query;

use skeeks\cms\components\Cms;

class SsSharesQuery extends \common\models\generated\query\SsSharesQuery
{
    public function active ()
    {
        return $this
            ->andWhere([
                'active' => Cms::BOOL_Y,
            ]);
    }

    public function hasImage ()
    {
        return $this
            ->andWhere([
                'NOT', ['image_id' => null],
            ]);
    }

    public function bannerType ($type)
    {
        return $this
            ->andWhere([
                'banner_type' => $type,
            ]);
    }

    public function showTime ($time)
    {
        return $this
            ->andWhere([
                'AND',
                ['<=', 'begin_datetime', $time],
                ['>=', 'end_datetime', $time],
            ]);
    }


    public function orderByBeginDatetime ($direction = SORT_ASC)
    {
        return $this
            ->orderBy([
                'begin_datetime' => $direction,
                'id' => $direction,
            ]);
    }

}