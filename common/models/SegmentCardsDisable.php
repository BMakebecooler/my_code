<?php


namespace common\models;


class SegmentCardsDisable extends \common\models\generated\models\SegmentCardsDisable
{
    public static function getCardsDisable($segmentId)
    {
        $return = [];
        $query = self::find()
            ->andWhere(['segment_id' => $segmentId]);

        foreach ($query->each() as $model){
            $return[] = $model->card_id;
        }

        return $return;

    }
}