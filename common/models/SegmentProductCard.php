<?php


namespace common\models;


class SegmentProductCard extends \common\models\generated\models\SegmentProductCard
{
    public static function getCardsCount($segmentId)
    {
       return SegmentProductCard::find()
            ->select(['card_id'])
            ->andWhere(['segment_id' => $segmentId])
            ->count();
    }
}